<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class jarvis extends eqLogic {
	/*     * *************************Attributs****************************** */

	public static $_configParam = array('bing_speech_api_key', 'check_updates', 'command_stt', 'conversation_mode', 'dictionary', 'google_speech_api_key', 'language', 'language_model', 'max_noise_duration_to_kill', 'min_noise_duration_to_start', 'min_noise_perc_to_start', 'min_silence_duration_to_stop', 'min_silence_level_to_stop', 'osx_say_voice', 'phrase_failed', 'phrase_misunderstood', 'phrase_triggered', 'phrase_welcome', 'play_hw', 'pocketsphinxlog', 'rec_hw', 'separator', 'snowboy_sensitivity', 'tmp_folder', 'trigger', 'trigger_mode', 'trigger_stt', 'tts_engine', 'username', 'wit_server_access_token');

	public static $_installationDir = '/opt/jarvis';

	/*     * ***********************Methode static*************************** */

	/*     * *********************Méthodes d'instance************************* */

	public function postSave() {
		$this->writeConfig();
	}

	public function writeConfig() {
		if ($this->execCmd('sudo ls ' . self::$_installationDir . '/config 2>/dev/null | wc -l') == 0) {
			$this->execCmd('sudo mkdir -p ' . self::$_installationDir . '/config');
		}
		foreach (self::$_configParam as $param) {
			if ($this->getConfiguration('jarvis::' . $param, null) === null) {
				continue;
			}
			$this->execCmd('sudo echo ' . $this->getConfiguration('jarvis::' . $param) . ' > ' . self::$_installationDir . '/config/' . $param);
		}
	}

	public function copyFile($_from, $_to) {
		if ($this->getConfiguration('mode', 'local') == 'local') {
			shell_exec('sudo cp ' . $_from . ' ' . $_to);
		}
		if ($this->getConfiguration('mode') == 'ssh') {
			$connection = ssh2_connect($this->getConfiguration('ssh::ip'), $this->getConfiguration('ssh::port', 22));
			ssh2_auth_password($connection, $this->getConfiguration('ssh::username'), $this->getConfiguration('ssh::password'));
			ssh2_scp_send($connection, $_from, $_to, 0777);
		}
	}

	public function readFile($_file) {
		if ($this->getConfiguration('mode', 'local') == 'local') {
			return shell_exec('sudo cat ' . $_file);
		}
		if ($this->getConfiguration('mode') == 'ssh') {
			$connection = ssh2_connect($this->getConfiguration('ssh::ip'), $this->getConfiguration('ssh::port', 22));
			ssh2_auth_password($connection, $this->getConfiguration('ssh::username'), $this->getConfiguration('ssh::password'));
			$stream = ssh2_exec($connection, 'sudo cat ' . $_file);
			stream_set_blocking($stream, true);
			return stream_get_contents($stream);
		}
	}

	public function execCmd($_cmd) {
		if ($this->getConfiguration('mode', 'local') == 'local') {
			return shell_exec($_cmd);
		}
		if ($this->getConfiguration('mode') == 'ssh') {
			$_cmd = str_replace('sudo ', '', $_cmd);
			$connection = ssh2_connect($this->getConfiguration('ssh::ip'), $this->getConfiguration('ssh::port', 22));
			ssh2_auth_password($connection, $this->getConfiguration('ssh::username'), $this->getConfiguration('ssh::password'));
			$stream = ssh2_exec($connection, $_cmd);
			stream_set_blocking($stream, true);
			return stream_get_contents($stream);
		}
	}

	/*     * **********************Getteur Setteur*************************** */
}

class jarvisCmd extends cmd {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */

	public function execute($_options = array()) {

	}

	/*     * **********************Getteur Setteur*************************** */
}

?>
