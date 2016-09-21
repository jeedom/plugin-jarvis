<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('jarvis');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
  <div class="col-lg-2 col-md-3 col-sm-4">
    <div class="bs-sidebar">
      <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
        <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter un Jarvis}}</a>
        <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
        <?php
foreach ($eqLogics as $eqLogic) {
	echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
}
?>
     </ul>
   </div>
 </div>

 <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
  <legend>{{Mes Jarvis}}</legend>
  <legend><i class="fa fa-cog"></i>  {{Gestion}}</legend>
  <div class="eqLogicThumbnailContainer">
    <div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
     <center>
      <i class="fa fa-plus-circle" style="font-size : 6em;color:#94ca02;"></i>
    </center>
    <span style="font-size : 1.1em;position:relative; top : 23px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#94ca02"><center>{{Ajouter}}</center></span>
  </div>
  <div class="cursor eqLogicAction" data-action="gotoPluginConf" style="background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;">
    <center>
      <i class="fa fa-wrench" style="font-size : 6em;color:#767676;"></i>
    </center>
    <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#767676"><center>{{Configuration}}</center></span>
  </div>
</div>
<legend><i class="fa fa-table"></i> {{Mes Jarvis}}</legend>
<div class="eqLogicThumbnailContainer">
  <?php
foreach ($eqLogics as $eqLogic) {
	echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >';
	echo "<center>";
	echo '<img src="' . $plugin->getPathImgIcon() . '" height="105" width="95" />';
	echo "</center>";
	echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
	echo '</div>';
}
?>
</div>
</div>

<div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
	<a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
  <a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>

  <ul class="nav nav-tabs" role="tablist">
   <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Equipement}}</a></li>
   <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
 </ul>

 <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
   <div role="tabpanel" class="tab-pane active" id="eqlogictab">
    <form class="form-horizontal">
      <fieldset>
        <legend><i class="fa fa-arrow-circle-left eqLogicAction cursor" data-action="returnToThumbnailDisplay"></i> {{Général}}  <i class='fa fa-cogs eqLogicAction pull-right cursor expertModeVisible' data-action='configure'></i></legend>
        <div class="form-group">
          <label class="col-sm-3 control-label">{{Nom de l'équipement Jarvis}}</label>
          <div class="col-sm-3">
            <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
            <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement template}}"/>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-3 control-label" >{{Objet parent}}</label>
          <div class="col-sm-3">
            <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
              <option value="">{{Aucun}}</option>
              <?php
foreach (object::all() as $object) {
	echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
}
?>
           </select>
         </div>
       </div>
       <div class="form-group">
        <label class="col-sm-3 control-label"></label>
        <div class="col-sm-9">
         <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
         <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
       </div>
     </div>
     <div class="form-group">
      <label class="col-sm-3 control-label">{{Relancer automatiquement en cas d'arrêt non voulu}}</label>
      <div class="col-sm-4">
        <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="autorestart" />
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-3 control-label">{{Rediriger les reponses de Jeedom sur}}</label>
      <div class="col-sm-3">
       <div class="input-group">
        <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="redirectJeedomResponse" />
        <span class="input-group-btn">
         <a class="btn btn-default" id="bt_selectRedirectJeedomResponse"><i class="fa fa-list-alt"></i></a>
       </span>
     </div>
   </div>
 </div>

 <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
      <h4 class="panel-title">
        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
          <i class="fa fa-plug" aria-hidden="true"></i> {{Mode et connexion}}
        </a>
      </h4>
    </div>
    <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
      <div class="panel-body">
        <div class="form-group">
          <label class="col-sm-3 control-label">{{Mode}}</label>
          <div class="col-sm-3">
            <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="mode">
              <option value="local">{{Local}}</option>
              <option value="ssh">{{SSH}}</option>
            </select>
          </div>
        </div>
        <div class="jarvis_mode jarvis_ssh">
          <div class="form-group">
            <label class="col-sm-3 control-label">{{IP}}</label>
            <div class="col-sm-3">
              <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ssh::ip" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">{{Nom d'utilisateur}}</label>
            <div class="col-sm-3">
              <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ssh::username" />
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">{{Mot de passe}}</label>
            <div class="col-sm-3">
              <input type="password" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ssh::password" />
            </div>
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-3 control-label">{{Répertoire d'installation}}</label>
          <div class="col-sm-3">
            <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis_install_folder" />
          </div>
        </div>
        <div class="form-group">
          <label class="col-sm-3 control-label">{{Action}}</label>
          <div class="col-sm-9">
            <a class="btn btn-warning" id="bt_installJarvis"><i class="fa fa-play"></i> {{Installer Jarvis}}</a>
            <a class="btn btn-success" id="bt_updateJarvis"><i class="fa fa-refresh"></i> {{Mettre à jour}}</a>
            <a class="btn btn-success" id="bt_sendConfiguration"><i class="fa fa-wrench"></i> {{Envoyer la configuration}}</a>
            <a class="btn btn-default" id="bt_viewInstallLog"><i class="fa fa-file-code-o"></i> {{Voir log d'installation}}</a>
            <a class="btn btn-default" id="bt_viewLog"><i class="fa fa-file-o"></i> {{Voir log Jarvis}}</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingTwo">
      <h4 class="panel-title">
        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
          <i class="fa fa-wrench" aria-hidden="true"></i> {{Général}}
        </a>
      </h4>
    </div>
    <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
      <div class="panel-body">
       <div class="form-group">
        <label class="col-sm-3 control-label">{{Comment Jarvis doit-il vous appeller}}</label>
        <div class="col-sm-3">
          <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::username" />
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-3 control-label">{{Déclencheur de l'écoute}}</label>
        <div class="col-sm-3">
          <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::trigger_mode" >
            <option value="enter_key">{{Appuie sur la touche entrée}}</option>
            <option value="magic_word">{{Mot magique}}</option>
            <option value="physical_button">{{Appuie sur un bouton}}</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-3 control-label">{{Mot magique de début de conversation}}</label>
        <div class="col-sm-3">
          <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::trigger" />
        </div>
      </div>
       <div class="form-group">
        <label class="col-sm-3 control-label">{{Mot magique de fin de conversation}}</label>
        <div class="col-sm-3">
          <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::trigger_end" />
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-3 control-label">{{Langue}}</label>
        <div class="col-sm-3">
          <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::language" >
            <option value="fr_FR">{{Français}}</option>
            <option value="en_EN">{{Anglais}}</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-3 control-label">{{Mode conversation}}</label>
        <div class="col-sm-3">
          <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::conversation_mode" >
            <option value="true">{{Oui}}</option>
            <option value="false">{{Non}}</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-3 control-label">{{Vérifier les mises à jour au démarrage de Jarvis}}</label>
        <div class="col-sm-3">
          <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::check_updates" >
            <option value="true">{{Oui}}</option>
            <option value="false">{{Non}}</option>
          </select>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="panel panel-default">
  <div class="panel-heading" role="tab" id="headingThree">
    <h4 class="panel-title">
      <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
        <i class="fa fa-commenting-o" aria-hidden="true"></i> {{Phrases}}
      </a>
    </h4>
  </div>
  <div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
    <div class="panel-body">
      <div class="form-group">
        <label class="col-sm-3 control-label">{{Phrase de démarrage}}</label>
        <div class="col-sm-3">
          <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::phrase_welcome" />
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-3 control-label">{{Phrase de confirmation d'écoute}}</label>
        <div class="col-sm-3">
          <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::phrase_triggered" />
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-3 control-label">{{Phrase de fin d'écoute}}</label>
        <div class="col-sm-3">
          <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::phrase_triggered_end" />
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-3 control-label">{{Phrase lors d'une commande non reconnue}}</label>
        <div class="col-sm-3">
          <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::phrase_misunderstood" />
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-3 control-label">{{Phrase lors de l'échec d'éxecution}}</label>
        <div class="col-sm-3">
          <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::phrase_failed" />
        </div>
      </div>
    </div>
  </div>
</div>

<div class="panel panel-default">
  <div class="panel-heading" role="tab" id="headingFour">
    <h4 class="panel-title">
      <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
        <i class="fa fa-volume-off" aria-hidden="true"></i> {{Audio}}
      </a>
    </h4>
  </div>
  <div id="collapseFour" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingFour">
    <div class="panel-body">
     <div class="form-group">
      <label class="col-sm-3 control-label">{{Haut parleur}}</label>
      <div class="col-sm-4">
        <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::play_hw" ></select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">{{Micro}}</label>
      <div class="col-sm-4">
        <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::rec_hw" ></select>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">{{Durée du bruit pour commencer l'écoute}}</label>
      <div class="col-sm-3">
        <input type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::min_noise_duration_to_start" />
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">{{Pourcentage de bruit pour commencer l'écoute}}</label>
      <div class="col-sm-3">
        <input type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::min_noise_perc_to_start" />
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">{{Durée du silence pour stoper l'écoute}}</label>
      <div class="col-sm-3">
        <input type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::min_silence_duration_to_stop" />
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">{{Pourcentage de silence pour arrêter l'écoute}}</label>
      <div class="col-sm-3">
        <input type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::min_silence_level_to_stop" />
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label">{{Durée maximum de l'écoute}}</label>
      <div class="col-sm-3">
        <input type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::max_noise_duration_to_kill" />
      </div>
    </div>
  </div>
</div>
</div>

<div class="panel panel-default">
  <div class="panel-heading" role="tab" id="headingFive">
    <h4 class="panel-title">
      <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
        <i class="fa fa-microphone" aria-hidden="true"></i> {{Reconnaissance vocale}}
      </a>
    </h4>
  </div>
  <div id="collapseFive" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingFive">
    <div class="panel-body">
      <div class="form-group">
        <label class="col-sm-3 control-label">{{Méthode de reconnaissance du mot magique}}</label>
        <div class="col-sm-3">
          <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::trigger_stt" >
           <option value="snowboy">{{Snowboy}}</option>
           <option value="google">{{Google}}</option>
         </select>
       </div>
     </div>

     <div class="form-group">
      <label class="col-sm-3 control-label">{{Méthode de reconnaissance de la commande}}</label>
      <div class="col-sm-3">
        <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::command_stt" >
         <option value="bing">{{Bing}}</option>
         <option value="google">{{Google}}</option>
         <option value="wit">{{Wit}}</option>
       </select>
     </div>
   </div>
   <div class="form-group">
    <label class="col-sm-3 control-label">{{Sensibilité snowboy (0 à 1)}}</label>
    <div class="col-sm-3">
      <input type="number" min="0" max="1" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::snowboy_sensitivity" />
    </div>
  </div>

  <div class="form-group">
    <label class="col-lg-3 control-label">{{Envoyer fichier mot magique (de début ou de fin) snowboy}}</label>
    <div class="col-lg-8">
      <span class="btn btn-default btn-file">
        <i class="fa fa-cloud-upload"></i> {{Envoyer}}<input  id="bt_uploadMagicWordSnowboy" type="file" name="file" style="display: inline-block;">
      </span>
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-3 control-label">{{Clef Bing}}</label>
    <div class="col-sm-3">
      <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::bing_speech_api_key" />
    </div>
  </div>
  <div class="form-group">
    <label class="col-sm-3 control-label">{{Clef Google}}</label>
    <div class="col-sm-3">
      <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::google_speech_api_key" />
    </div>
  </div>
  <div class="form-group">
    <label class="col-sm-3 control-label">{{Clef Wit}}</label>
    <div class="col-sm-3">
      <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::wit_server_access_token" />
    </div>
  </div>
</div>
</div>
</div>

<div class="panel panel-default">
  <div class="panel-heading" role="tab" id="headingSix">
    <h4 class="panel-title">
      <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
        <i class="fa fa-play" aria-hidden="true"></i> {{Synthese vocale}}
      </a>
    </h4>
  </div>
  <div id="collapseSix" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingSix">
    <div class="panel-body">
      <div class="form-group">
        <label class="col-sm-3 control-label">{{Moteur de synthèse vocal}}</label>
        <div class="col-sm-3">
          <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="jarvis::tts_engine" >
           <option value="svox_pico">{{Svox pico}}</option>
           <option value="google">{{Google}}</option>
           <option value="espeak">{{Espeak}}</option>
         </select>
       </div>
     </div>
   </div>
 </div>
</div>
</div>
</fieldset>
</form>
</div>
<div role="tabpanel" class="tab-pane" id="commandtab">
  <a class="btn btn-success btn-sm cmdAction pull-right" data-action="add"><i class="fa fa-plus-circle"></i> {{Commandes}}</a><br/><br/>
  <table id="table_cmd" class="table table-bordered table-condensed">
    <thead>
      <tr>
        <th style="max-width:360px;">{{Nom}}</th><th>{{Type}}</th><th>{{Action}}</th>
      </tr>
    </thead>
    <tbody>
    </tbody>
  </table>
</div>
</div>

</div>
</div>

<?php include_file('desktop', 'jarvis', 'js', 'jarvis');?>
<?php include_file('core', 'plugin.template', 'js');?>
