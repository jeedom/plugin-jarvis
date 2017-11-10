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

	/*     * ***********************Methode static*************************** */

	public static function event() {
		$jarvis = jarvis::byId(init('id'));
		if (!is_object($jarvis)) {
			throw new Exception(__('Equipement introuvable :', __FILE__) . ' ' . init('id'));
		}
		$query = init('query');
		if (init('utf8', 0) == 1) {
			$query = utf8_encode($query);
		}
		$param = array('plugin' => 'jarvis');
		if (init('emptyReply') != '') {
			$param['emptyReply'] = init('emptyReply');
		}
		if (init('profile') != '') {
			$param['profile'] = init('profile');
		}
		$say = $jarvis->getCmd(null, 'say');
		if (is_object($say) && $say->getCache('storeVariable', 'none') != 'none') {
			$say->askResponse($query);
			return;
		}
		$response = interactQuery::tryToReply($query, $param);
		if ($jarvis->getConfiguration('redirectJeedomResponse') == '') {
			echo $response['reply'];
		} else {
			$cmd = cmd::byId(str_replace('#', '', $jarvis->getConfiguration('redirectJeedomResponse')));
			if (!is_object($cmd)) {
				throw new Exception(__('Commande de réponse introuvable :', __FILE__) . ' ' . $jarvis->getConfiguration('redirectJeedomResponse'));
			}
			$cmd->execCmd(array('message' => $response['reply']));
		}
		return;
	}

	public static function cron10($_eqLogic_id = null) {
		if ($_eqLogic_id == null) {
			$eqLogics = eqLogic::byType('jarvis');
		} else {
			$eqLogics = array(eqLogic::byId($_eqLogic_id));
		}
		foreach ($eqLogics as $jarvis) {
			try {
				$jarvis->updateInfo();
				$state = $jarvis->getCmd(null, 'state');
				if ($jarvis->getConfiguration('autorestart') == 1 && is_object($state) && !$state->execCmd() && $jarvis->getCache('deamonState') == 'start') {
					$jarvis->deamonManagement('start');
				}
			} catch (Exception $e) {
				log::add('jarvis', 'error', $e->getMessage());
			}
		}
	}

	/*     * *********************Méthodes d'instance************************* */

	public function installJarvis($_mode = 'install') {
		$this->copyFile(dirname(__FILE__) . '/../../resources/install.sh', '/tmp/jarvis_install.sh');
		$this->execCmd('sudo chmod +x /tmp/jarvis_install.sh;sudo /tmp/jarvis_install.sh "' . $this->getConfiguration('jarvis_install_folder') . '" ' . $_mode . '> /tmp/jarvis_installation.log 2>&1 &');
	}

	public function updateInfo() {
		$state_info = $this->deamonManagement('info');
		$state = $this->getCmd(null, 'state');
		if (is_object($state) && $state->formatValue($state_info) != $state->execCmd()) {
			$state->event($state_info);
		}
	}

	public function deamonManagement($_action = 'info') {
		switch ($_action) {
			case 'info':
				return ($this->execCmd('sudo ps -ax | grep jarvis.sh | grep -v grep | wc -l') != 0);
			case 'start':
				$this->deamonManagement('stop');
				$volume = $this->getCmd(null, 'volume');
				if (is_object($volume) && $volume->getLastValue() !== '') {
					$card = substr(str_replace('hw:', '', $this->getConfiguration('jarvis::play_hw')), 0, 1);
					$this->execCmd('sudo amixer -c ' . $card . ' set PCM ' . $volume->getLastValue() . '%');
				}
				echo "\n";
				$sensitivity = $this->getCmd(null, 'sensitivity');
				if (is_object($sensitivity) && $sensitivity->getLastValue() !== '') {
					$card = substr(str_replace('hw:', '', $this->getConfiguration('jarvis::rec_hw')), 0, 1);
					$this->execCmd('sudo amixer -c ' . $card . ' set Mic ' . $volume->getLastValue() . '%');
				}
				$this->execCmd('sudo ' . $this->getConfiguration('jarvis_install_folder') . '/jarvis.sh -b');
				sleep(3);
				if (strpos($this->readFile($this->getConfiguration('jarvis_install_folder') . '/jarvis.log'), 'snowboy recognition failed') !== false) {
					$this->deamonManagement('stop');
					sleep(3);
					$this->execCmd('sudo ' . $this->getConfiguration('jarvis_install_folder') . '/jarvis.sh -b');
					sleep(4);
					if (strpos($this->readFile($this->getConfiguration('jarvis_install_folder') . '/jarvis.log'), 'snowboy recognition failed') !== false) {
						$this->deamonManagement('stop');
						$this->updateInfo();
						throw new Exception(__('Impossible de démarrer jarvis, essayer de débrancher le micro et de le rebrancher', __FILE__));
					}
				}
				break;
			case 'stop':
				$cmd = "(ps ax || ps w) | grep -ie 'jarvis.sh' | grep -v grep | awk '{print $1}' | xargs kill -9 > /dev/null 2>&1";
				$cmd .= "; (ps ax || ps w) | grep -ie 'jarvis.sh' | grep -v grep | awk '{print $1}' | xargs sudo kill -9 > /dev/null 2>&1";
				$this->execCmd($cmd);
				$cmd = "(ps ax || ps w) | grep -ie 'snowboy' | grep -v grep | awk '{print $1}' | xargs kill -9 > /dev/null 2>&1";
				$cmd .= "; (ps ax || ps w) | grep -ie 'snowboy' | grep -v grep | awk '{print $1}' | xargs sudo kill -9 > /dev/null 2>&1";
				$this->execCmd($cmd);
				break;
		}
	}

	public function preInsert() {
		$this->setConfiguration('mode', 'local');
		$this->setConfiguration('jarvis_install_folder', '/opt/jarvis');
		$this->setConfiguration('jarvis::username', __('Monsieur', __FILE__));
		$this->setConfiguration('jarvis::check_updates', 'true');
		$this->setConfiguration('jarvis::command_stt', 'google');
		$this->setConfiguration('jarvis::conversation_mode', 'true');
		$this->setConfiguration('jarvis::phrase_failed', __('Echec de la commande', __FILE__));
		$this->setConfiguration('jarvis::phrase_triggered', __('Oui', __FILE__));
		$this->setConfiguration('jarvis::phrase_welcome', __('Bonjour', __FILE__));
		$this->setConfiguration('jarvis::phrase_misunderstood', __('je n\'ai pas compris', __FILE__));
		$this->setConfiguration('jarvis::language', 'fr_FR');
		$this->setConfiguration('jarvis::max_noise_duration_to_kill', 10);
		$this->setConfiguration('jarvis::min_noise_duration_to_start', 0.1);
		$this->setConfiguration('jarvis::min_noise_perc_to_start', 1);
		$this->setConfiguration('jarvis::min_silence_duration_to_stop', 0.4);
		$this->setConfiguration('jarvis::min_silence_level_to_stop', 5);
		$this->setConfiguration('jarvis::snowboy_sensitivity', 0.5);
		$this->setConfiguration('jarvis::trigger', 'ok jeedom');
		$this->setConfiguration('jarvis::trigger_mode', 'magic_word');
		$this->setConfiguration('jarvis::trigger_stt', 'snowboy');
		$this->setConfiguration('jarvis::tts_engine', 'svox_pico');
		$this->setConfiguration('jarvis::volume', 83);
		$this->setConfiguration('jarvis::sensitivity', 83);
	}

	public function preUpdate() {
		if ($this->getConfiguration('jarvis_install_folder') == '') {
			throw new Exception(__('Le répertoire d\'installation de Jarvis ne peut être vide', __FILE__));
		}
		$this->setConfiguration('jarvis::trigger', trim(strtolower($this->getConfiguration('jarvis::trigger'))));
	}

	public function postSave() {
		$cmd = $this->getCmd(null, 'state');
		if (!is_object($cmd)) {
			$cmd = new jarvisCmd();
			$cmd->setName(__('Status', __FILE__));
			$cmd->setOrder(1);
			$cmd->setTemplate('dashboard', 'line');
			$cmd->setTemplate('mobile', 'line');
		}
		$cmd->setEqLogic_id($this->getId());
		$cmd->setLogicalId('state');
		$cmd->setType('info');
		$cmd->setSubType('binary');
		$cmd->save();

		$cmd = $this->getCmd(null, 'start');
		if (!is_object($cmd)) {
			$cmd = new jarvisCmd();
			$cmd->setName(__('Démarrer', __FILE__));
			$cmd->setOrder(2);
		}
		$cmd->setEqLogic_id($this->getId());
		$cmd->setLogicalId('start');
		$cmd->setType('action');
		$cmd->setSubType('other');
		$cmd->save();

		$cmd = $this->getCmd(null, 'stop');
		if (!is_object($cmd)) {
			$cmd = new jarvisCmd();
			$cmd->setName(__('Arrêter', __FILE__));
			$cmd->setOrder(3);
		}
		$cmd->setEqLogic_id($this->getId());
		$cmd->setLogicalId('stop');
		$cmd->setType('action');
		$cmd->setSubType('other');
		$cmd->save();

		$cmd = $this->getCmd(null, 'listen');
		if (!is_object($cmd)) {
			$cmd = new jarvisCmd();
			$cmd->setName(__('Ecouter', __FILE__));
			$cmd->setOrder(3);
		}
		$cmd->setEqLogic_id($this->getId());
		$cmd->setLogicalId('listen');
		$cmd->setType('action');
		$cmd->setSubType('other');
		$cmd->save();

		$cmd = $this->getCmd(null, 'volume');
		if (!is_object($cmd)) {
			$cmd = new jarvisCmd();
			$cmd->setName(__('Volume', __FILE__));
			$cmd->setOrder(4);
		}
		$cmd->setEqLogic_id($this->getId());
		$cmd->setLogicalId('volume');
		$cmd->setType('action');
		$cmd->setSubType('slider');
		$cmd->save();

		$cmd = $this->getCmd(null, 'sensitivity');
		if (!is_object($cmd)) {
			$cmd = new jarvisCmd();
			$cmd->setName(__('Sensibilité', __FILE__));
			$cmd->setOrder(5);
		}
		$cmd->setEqLogic_id($this->getId());
		$cmd->setLogicalId('sensitivity');
		$cmd->setType('action');
		$cmd->setSubType('slider');
		$cmd->save();

		$cmd = $this->getCmd(null, 'say');
		if (!is_object($cmd)) {
			$cmd = new jarvisCmd();
			$cmd->setName(__('Dit', __FILE__));
			$cmd->setOrder(6);
		}
		$cmd->setEqLogic_id($this->getId());
		$cmd->setLogicalId('say');
		$cmd->setType('action');
		$cmd->setDisplay('title_disable', 1);
		$cmd->setSubType('message');
		$cmd->save();

		$refresh = $this->getCmd(null, 'refresh');
		if (!is_object($refresh)) {
			$refresh = new jarvisCmd();
		}
		$refresh->setName(__('Rafraîchir', __FILE__));
		$refresh->setEqLogic_id($this->getId());
		$refresh->setLogicalId('refresh');
		$refresh->setType('action');
		$refresh->setSubType('other');
		$refresh->save();
	}

	public function getSpeakerOrMicro($_type = 'speaker') {
		$result = array();
		if (!$this->getIsEnable()) {
			return $result;
		}
		switch ($_type) {
			case 'speaker':
				$datas = explode("\n", $this->execCmd('sudo aplay -l | grep -E "card|carte"'));
				break;
			case 'micro':
				$datas = explode("\n", $this->execCmd('sudo arecord -l | grep -E "card|carte"'));
				break;
			default:
				return $result;
				break;
		}
		foreach ($datas as $data) {
			if (trim($data) == '') {
				continue;
			}
			preg_match('/.*card(.*?):(.*),.*device(.*?):(.*)/', $data, $matches);
			if (count($matches) != 5) {
				continue;
			}
			$result['hw:' . trim($matches[1]) . ',' . trim($matches[3])] = trim($matches[2]) . ' ' . trim($matches[4]) . ' (' . trim($matches[1]) . ',' . trim($matches[3]) . ')';
		}
		foreach ($datas as $data) {
			if (trim($data) == '') {
				continue;
			}
			preg_match('/.*carte(.*?):(.*),.*périphérique(.*?):(.*)/', $data, $matches);
			if (count($matches) != 5) {
				continue;
			}
			$result['hw:' . trim($matches[1]) . ',' . trim($matches[3])] = trim($matches[2]) . ' ' . trim($matches[4]) . ' (' . trim($matches[1]) . ',' . trim($matches[3]) . ')';
		}
		return $result;
	}

	public function writeConfig() {
		if ($this->execCmd('sudo ls ' . $this->getConfiguration('jarvis_install_folder') . '/config 2>/dev/null | wc -l') == 0) {
			return;
		}
		$this->deamonManagement('stop');
		$this->execCmd('sudo chmod 777 -R ' . $this->getConfiguration('jarvis_install_folder'));
		foreach (self::$_configParam as $param) {
			if ($this->getConfiguration('jarvis::' . $param, null) === null) {
				continue;
			}
			$this->execCmd('sudo echo ' . $this->getConfiguration('jarvis::' . $param) . ' > ' . $this->getConfiguration('jarvis_install_folder') . '/config/' . $param);
		}
		$cmd = 'sudo echo "#Variable en entree">' . $this->getConfiguration('jarvis_install_folder') . '/jeedom.sh;';
		$cmd .= 'sudo echo "var_tmp=\$@">>' . $this->getConfiguration('jarvis_install_folder') . '/jeedom.sh;';
		$cmd .= 'sudo echo "jrv_var_apl=\$(echo \$var_tmp | sed \'s/ /\%20/g\')" >>' . $this->getConfiguration('jarvis_install_folder') . '/jeedom.sh;';
		$cmd .= 'sudo echo "curl -s \"' . network::getNetworkAccess('internal') . '/core/api/jeeApi.php?apikey=' . config::byKey('api') . '&type=jarvis&id=' . $this->getId() . '&query=\$jrv_var_apl\"" >> ' . $this->getConfiguration('jarvis_install_folder') . '/jeedom.sh;sudo chmod +x ' . $this->getConfiguration('jarvis_install_folder') . '/jeedom.sh';
		$this->execCmd($cmd);
		$cmd = 'sudo rm -rf ' . $this->getConfiguration('jarvis_install_folder') . '/jarvis-commands;';
		if ($this->getConfiguration('jarvis::trigger_end') != '') {
			$cmd .= 'sudo echo \'' . $this->getConfiguration('jarvis::trigger_end') . '==bypass=false; say "' . $this->getConfiguration('jarvis::phrase_triggered_end', __('Au revoir', __FILE__)) . '"\' >> ' . $this->getConfiguration('jarvis_install_folder') . '/jarvis-commands;';
		}
		$cmd .= 'sudo echo \'(*)==say "$(' . $this->getConfiguration('jarvis_install_folder') . '/jeedom.sh \"(1)\")"\' >> ' . $this->getConfiguration('jarvis_install_folder') . '/jarvis-commands';
		$this->execCmd($cmd);
		foreach (ls(dirname(__FILE__) . '/../../resources', '*.pmdl') as $files) {
			$this->copyFile(dirname(__FILE__) . '/../../resources/' . $files, $this->getConfiguration('jarvis_install_folder') . '/stt_engines/snowboy/resources/' . strtolower($files));
		}
		foreach (ls(dirname(__FILE__) . '/../../data', '*.pmdl') as $files) {
			$this->copyFile(dirname(__FILE__) . '/../../data/' . $files, $this->getConfiguration('jarvis_install_folder') . '/stt_engines/snowboy/data/' . strtolower($files));
		}
		if ($this->getCache('deamonState') == 'start') {
			$this->deamonManagement('start');
		}
	}

	public function copyFile($_from, $_to) {
		if ($this->getConfiguration('mode', 'local') == 'local') {
			shell_exec('sudo cp ' . $_from . ' ' . $_to);
		}
		if ($this->getConfiguration('mode') == 'ssh') {
			$connection = ssh2_connect($this->getConfiguration('ssh::ip'), $this->getConfiguration('ssh::port', 22));
			if ($connection === false) {
				throw new Exception(__('Impossible de se connecter sur :', __FILE__) . ' ' . $this->getConfiguration('ssh::ip') . ':' . $this->getConfiguration('ssh::port', 22));
			}
			$auth = @ssh2_auth_password($connection, $this->getConfiguration('ssh::username'), $this->getConfiguration('ssh::password'));
			if ($auth === false) {
				throw new Exception(__('Echec de l\'authentification SSH', __FILE__));
			}
			ssh2_scp_send($connection, $_from, $_to, 0777);
		}
	}

	public function readFile($_file) {
		if ($this->getConfiguration('mode', 'local') == 'local') {
			return shell_exec('sudo cat ' . $_file);
		}
		if ($this->getConfiguration('mode') == 'ssh') {
			$connection = ssh2_connect($this->getConfiguration('ssh::ip'), $this->getConfiguration('ssh::port', 22));
			if ($connection === false) {
				throw new Exception(__('Impossible de se connecter sur :', __FILE__) . ' ' . $this->getConfiguration('ssh::ip') . ':' . $this->getConfiguration('ssh::port', 22));
			}
			$auth = @ssh2_auth_password($connection, $this->getConfiguration('ssh::username'), $this->getConfiguration('ssh::password'));
			if ($auth === false) {
				throw new Exception(__('Echec de l\'authentification SSH', __FILE__));
			}
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
			if ($this->getConfiguration('ssh::username') == 'root') {
				$_cmd = str_replace('sudo ', '', $_cmd);
			}
			$connection = ssh2_connect($this->getConfiguration('ssh::ip'), $this->getConfiguration('ssh::port', 22));
			if ($connection === false) {
				throw new Exception(__('Impossible de se connecter sur :', __FILE__) . ' ' . $this->getConfiguration('ssh::ip') . ':' . $this->getConfiguration('ssh::port', 22));
			}
			$auth = @ssh2_auth_password($connection, $this->getConfiguration('ssh::username'), $this->getConfiguration('ssh::password'));
			if ($auth === false) {
				throw new Exception(__('Echec de l\'authentification SSH', __FILE__));
			}
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
		$eqLogic = $this->getEqLogic();
		if ($this->getLogicalId() == 'start' || $this->getLogicalId() == 'stop') {
			$eqLogic->deamonManagement($this->getLogicalId());
			$eqLogic->updateInfo();
			$eqLogic->setCache('deamonState', $this->getLogicalId());
		}
		if ($this->getLogicalId() == 'refresh') {
			$eqLogic->updateInfo();
		}
		if ($this->getLogicalId() == 'listen') {
			$eqLogic->execCmd('sudo ' . $eqLogic->getConfiguration('jarvis_install_folder') . '/jarvis.sh -l &');
		}
		if ($this->getLogicalId() == 'volume') {
			$card = substr(str_replace('hw:', '', $eqLogic->getConfiguration('jarvis::play_hw')), 0, 1);
			$eqLogic->execCmd('sudo amixer -c ' . $card . ' set PCM ' . $_options['slider'] . '%');
		}
		if ($this->getLogicalId() == 'sensitivity') {
			$card = substr(str_replace('hw:', '', $eqLogic->getConfiguration('jarvis::rec_hw')), 0, 1);
			$eqLogic->execCmd('sudo amixer -c ' . $card . ' set Mic ' . $_options['slider'] . '%');
		}
		if ($this->getLogicalId() == 'say') {
			$cmd = 'sudo ' . $eqLogic->getConfiguration('jarvis_install_folder') . '/jarvis.sh -s ' . escapeshellarg(trim($_options['message']));
			if (isset($_options['answer'])) {
				$cmd .= ';sudo ' . $eqLogic->getConfiguration('jarvis_install_folder') . '/jarvis.sh -l &';
			}
			$eqLogic->execCmd($cmd);

		}
	}

	/*     * **********************Getteur Setteur*************************** */
}

?>
