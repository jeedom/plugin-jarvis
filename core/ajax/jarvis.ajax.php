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

try {
	require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
	include_file('core', 'authentification', 'php');

	if (!isConnect('admin')) {
		throw new Exception(__('401 - Accès non autorisé', __FILE__));
	}

	if (init('action') == 'getLog') {
		$jarvis = jarvis::byId(init('id'));
		if (!is_object($jarvis)) {
			throw new Exception(__('Impossible de trouver l\'équipement :', __FILE__) . ' ' . init('id'));
		}
		if (init('log') == 'installation') {
			ajax::success(explode("\n", $jarvis->readFile('/tmp/jarvis_installation.log')));
		}
		if (init('log') == 'current') {
			ajax::success(explode("\n", $jarvis->readFile($jarvis->getConfiguration('jarvis_install_folder') . '/jarvis.log')));
		}
	}

	if (init('action') == 'install_jarvis') {
		$jarvis = jarvis::byId(init('id'));
		if (!is_object($jarvis)) {
			throw new Exception(__('Impossible de trouver l\'équipement :', __FILE__) . ' ' . init('id'));
		}
		ajax::success($jarvis->installJarvis(init('mode')));
	}

	if (init('action') == 'send_config') {
		$jarvis = jarvis::byId(init('id'));
		if (!is_object($jarvis)) {
			throw new Exception(__('Impossible de trouver l\'équipement :', __FILE__) . ' ' . init('id'));
		}
		ajax::success($jarvis->writeConfig());
	}

	if (init('action') == 'getSpeakerOrMicro') {
		$jarvis = jarvis::byId(init('id'));
		if (!is_object($jarvis)) {
			throw new Exception(__('Impossible de trouver l\'équipement :', __FILE__) . ' ' . init('id'));
		}
		ajax::success($jarvis->getSpeakerOrMicro(init('type')));
	}

	if (init('action') == 'uploadMagicWordSnowboy') {
		$jarvis = jarvis::byId(init('id'));
		if (!is_object($jarvis)) {
			throw new Exception(__('Impossible de trouver l\'équipement :', __FILE__) . ' ' . init('id'));
		}
		if (!isset($_FILES['file'])) {
			throw new Exception(__('Aucun fichier trouvé. Vérifié parametre PHP (post size limit)', __FILE__));
		}
		$extension = strtolower(strrchr($_FILES['file']['name'], '.'));
		if (!in_array($extension, array('.pmdl'))) {
			throw new Exception('Extension du fichier non valide (autorisé .pmdl) : ' . $extension);
		}
		if (filesize($_FILES['file']['tmp_name']) > 1000000) {
			throw new Exception(__('Le fichier est trop gros (maximum 1mo)', __FILE__));
		}
		if (!file_exists(dirname(__FILE__) . '/../../data')) {
			mkdir(dirname(__FILE__) . '/../../data');
		}
		if (!move_uploaded_file($_FILES['file']['tmp_name'], dirname(__FILE__) . '/../../data/' . trim(strtolower($_FILES['file']['name'])))) {
			throw new Exception(__('Impossible de déplacer le fichier temporaire', __FILE__));
		}
		ajax::success();
	}

	throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
	/*     * *********Catch exeption*************** */
} catch (Exception $e) {
	ajax::error(displayExeption($e), $e->getCode());
}
?>
