#!/bin/bash

function apt_install {
	sudo apt-get -y install "$@"
	if [ $? -ne 0 ]; then
		echo "could not install $@ - abort"
		rm /tmp/install_jarvis_in_progress
		exit 1
	fi
}

function pip_install {
	sudo pip install "$@"
	if [ $? -ne 0 ]; then
		echo "could not install $@ - abort"
		rm /tmp/install_jarvis_in_progress
		exit 1
	fi
}

if [ -f /tmp/install_jarvis_in_progress ]; then
	echo "Installation already in progress"
	exit 1
fi

touch /tmp/install_jarvis_in_progress

INSTALL_FOLDER=$1
if [ -z ${INSTALL_FOLDER} ]; then
	echo "Installation error, no dir install found - abort"
	rm /tmp/install_jarvis_in_progress
	exit 1
fi
echo 'Installation of jarvis in '${INSTALL_FOLDER}

apt-get update

if [ "$(uname)" == "Darwin" ]; then
	platform="osx"
	apt_install awk curl git iconv nano osascript perl sed sox wget
elif [ "$(expr substr $(uname -s) 1 5)" == "Linux" ]; then
	platform="linux"
	apt_install alsa-utils gawk curl git mpg123 nano perl sed sox wget whiptail
else
	echo "ERROR: Unsupported platform"
	rm /tmp/install_jarvis_in_progress
	exit 1
fi

if [ $? -ne 0 ]; then
	echo "Installation error - abort"
	rm /tmp/install_jarvis_in_progress
	exit 1
fi

mkdir -p ${INSTALL_FOLDER}
if [ $? -ne 0 ]; then
	echo "Installation error - abort"
	rm /tmp/install_jarvis_in_progress
	exit 1
fi

rm -rf ${INSTALL_FOLDER}
if [ $? -ne 0 ]; then
	echo "Installation error - abort"
	rm /tmp/install_jarvis_in_progress
	exit 1
fi

git clone https://github.com/jeedom/jarvis.git ${INSTALL_FOLDER}
if [ $? -ne 0 ]; then
	echo "Installation error - abort"
	rm /tmp/install_jarvis_in_progress
	exit 1
fi

if [ ! -f ${INSTALL_FOLDER}/_snowboydetect.so ]; then
	echo "Installation of snowboy"
	if [[ "$platform" == "linux" ]]; then
		apt_install python-pyaudio python3-pyaudio libatlas-base-dev
		binaries="rpi-arm-raspbian-8.0-1.0.2"
	else
		brew install portaudio
		binaries="osx-x86_64-1.0.2"
	fi
	wget https://bootstrap.pypa.io/get-pip.py
	sudo python get-pip.py
	if [ $? -ne 0 ]; then
		echo "Installation error - abort"
		rm /tmp/install_jarvis_in_progress
		exit 1
	fi
	rm get-pip.py
	pip_install pyaudio
	cd ${INSTALL_FOLDER}/stt_engines/snowboy
	wget https://s3-us-west-2.amazonaws.com/snowboy/snowboy-releases/$binaries.tar.bz2
	if [ $? -ne 0 ]; then
		echo "Installation error - abort"
		rm /tmp/install_jarvis_in_progress
		exit 1
	fi
	tar xvjf $binaries.tar.bz2
	if [ $? -ne 0 ]; then
		echo "Installation error - abort"
		rm /tmp/install_jarvis_in_progress
		exit 1
	fi
	rm $binaries.tar.bz2
	mv $binaries/_snowboydetect.so .
	cp $binaries/snowboydetect.py .
	cp $binaries/snowboydecoder.py .
	cp -r $binaries/resources .
	rm -rf $binaries
	echo "Installation of snowboy success"
fi

hash 'pico2wave' 2>/dev/null || {
	echo "Installation of svox"
	if [[ "$platform" == "linux" ]]; then
		apt_install libttspico-utils
		echo "Installation of svox success"
	else
		echo "SVOX Pico is not available on your platform"
	fi
}

cd ${INSTALL_FOLDER}
rm /tmp/install_jarvis_in_progress
echo "Installation of jarvis sucess"