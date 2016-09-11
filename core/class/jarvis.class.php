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

	public static function cron10($_eqLogic_id = null) {
		if ($_eqLogic_id == null) {
			$eqLogics = eqLogic::byType('jarvis');
		} else {
			$eqLogics = array(eqLogic::byId($_eqLogic_id));
		}
		foreach ($eqLogics as $jarvis) {
			try {
				$jarvis->updateInfo();
			} catch (Exception $e) {
				log::add('jarvis', 'error', $e->getMessage());
			}
		}
	}

	/*     * *********************Méthodes d'instance************************* */

	public function updateInfo() {
		$state_info = $this->deamonManagement('info');
		$state = $this->getCmd(null, 'state');
		if (is_object($state) && $state->formatValue($state_info) != $state->execCmd()) {
			$state->setCollectDate('');
			$state->event($state_info);
		}
	}

	public function deamonManagement($_action = 'info') {
		switch ($_action) {
			case 'info':
				return ($this->execCmd('sudo ps -ax | grep jarvis.sh | grep -v grep | wc -l') != 0);
			case 'start':
				$this->execCmd('sudo ' . $this->getConfiguration('jarvis_install_folder') . '/jarvis.sh -b');
				break;
			case 'stop':
				$cmd = "(ps ax || ps w) | grep -ie 'jarvis.sh' | grep -v grep | awk '{print $1}' | xargs kill -9 > /dev/null 2>&1";
				$cmd .= "; (ps ax || ps w) | grep -ie 'jarvis.sh' | grep -v grep | awk '{print $1}' | xargs sudo kill -9 > /dev/null 2>&1";
				$this->execCmd('sudo ' . $cmd);
				break;
		}
	}

	public function postSave() {
		$cmd = $this->getCmd(null, 'state');
		if (!is_object($cmd)) {
			$cmd = new jarvisCmd();
			$cmd->setName(__('Status', __FILE__));
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
		}
		$cmd->setEqLogic_id($this->getId());
		$cmd->setLogicalId('stop');
		$cmd->setType('action');
		$cmd->setSubType('other');
		$cmd->save();
	}

	public function postUpdate() {
		$this->writeConfig();
	}

	public function writeConfig() {
		if ($this->execCmd('sudo ls ' . $this->getConfiguration('jarvis_install_folder') . '/config 2>/dev/null | wc -l') == 0) {
			return;
		}
		$cmd = '';
		foreach (self::$_configParam as $param) {
			if ($this->getConfiguration('jarvis::' . $param, null) === null) {
				continue;
			}
			$cmd .= 'sudo echo ' . $this->getConfiguration('jarvis::' . $param) . ' > ' . $this->getConfiguration('jarvis_install_folder') . '/config/' . $param . ';';
		}
		$this->execCmd($cmd);
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
		}
	}

	/*     * **********************Getteur Setteur*************************** */
}

?>
