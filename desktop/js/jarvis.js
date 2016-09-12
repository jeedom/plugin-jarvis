
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

 $('.eqLogicAttr[data-l1key=configuration][data-l2key=mode]').on('change',function(){
    $('.jarvis_mode').hide();
    $('.jarvis_'+$(this).value()).show();
});

 $("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

 function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom}}">';
    tr += '</td>';
    tr += '<td>';
    tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
    tr += '</td>';
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
    tr += '</td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    if (isset(_cmd.type)) {
        $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
    }
    jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}

$('#bt_installJarvis').on('click',function(){
    bootbox.confirm('{{Etês vous sur de vouloir lancer l\'installation de Jarvis ? Ceci peut prendre jusqu\'a 1 heure. Vous pouvez suivre l\'avancement dans le log de l\'installation.N\'oubliez pas de resauvegarder votre équipement une fois l\'installation terminée.}}', function (result) {
        if (result) {
           $.ajax({
            type: "POST", 
            url: "plugins/jarvis/core/ajax/jarvis.ajax.php",
            data: {
                action: "install_jarvis",
                id: $('.eqLogicAttr[data-l1key=id]').value(),
            },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function (data) {
                if (data.state != 'ok') {
                    $('#div_alert').showAlert({message: data.result, level: 'danger'});
                    return;
                }
                $('#div_alert').showAlert({message: '{{Installation de Jarvis en cours}}', level: 'sucess'});
                $('#md_modal').dialog({title: "{{Log d'installation}}"});
                $('#md_modal').load('index.php?v=d&plugin=jarvis&modal=jarvis.log&id=' + $('.eqLogicAttr[data-l1key=id]').value()+'&log=installation').dialog('open');
            }
        });
       }
   });
});

$('#bt_viewInstallLog').on('click',function () {
    $('#md_modal').dialog({title: "{{Log d'installation}}"});
    $('#md_modal').load('index.php?v=d&plugin=jarvis&modal=jarvis.log&id=' + $('.eqLogicAttr[data-l1key=id]').value()+'&log=installation').dialog('open');
});

$('#bt_viewLog').on('click',function () {
    $('#md_modal').dialog({title: "{{Log de Jarvis}}"});
    $('#md_modal').load('index.php?v=d&plugin=jarvis&modal=jarvis.log&id=' + $('.eqLogicAttr[data-l1key=id]').value()+'&log=current').dialog('open');
});


$("#bt_selectRedirectJeedomResponse").on('click', function () {
    jeedom.cmd.getSelectModal({cmd: {type: 'action', subType: 'message'}}, function (result) {
        $('.eqLogicAttr[data-l1key=configuration][data-l2key=redirectJeedomResponse]').value(result.human);
    });
});


function printEqLogic(eqLogic){
   $.ajax({
    type: "POST", 
    url: "plugins/jarvis/core/ajax/jarvis.ajax.php",
    data: {
        action: "getSpeakerOrMicro",
        id: eqLogic.id,
        type : 'speaker'
    },
    dataType: 'json',
    error: function (request, status, error) {
        handleAjaxError(request, status, error);
    },
    success: function (data) {
        if (data.state != 'ok') {
            $('#div_alert').showAlert({message: data.result, level: 'danger'});
            return;
        }
        var options = '';
        for(var i in data.result){
            if(isset(eqLogic.configuration) && isset(eqLogic.configuration['jarvis::play_hw']) && eqLogic.configuration['jarvis::play_hw'] == i){
                options += '<option value="'+i+'" selected>'+data.result[i]+'</option>';
            }else{
                options += '<option value="'+i+'">'+data.result[i]+'</option>';
            }
        }
        $('.eqLogicAttr[data-l1key=configuration][data-l2key="jarvis::play_hw"]').empty().append(options);
    }
});

   $.ajax({
    type: "POST", 
    url: "plugins/jarvis/core/ajax/jarvis.ajax.php",
    data: {
        action: "getSpeakerOrMicro",
        id: eqLogic.id,
        type : 'micro'
    },
    dataType: 'json',
    error: function (request, status, error) {
        handleAjaxError(request, status, error);
    },
    success: function (data) {
        if (data.state != 'ok') {
            $('#div_alert').showAlert({message: data.result, level: 'danger'});
            return;
        }
        var options = '';
        for(var i in data.result){
            if(isset(eqLogic.configuration) && isset(eqLogic.configuration['jarvis::rec_hw']) && eqLogic.configuration['jarvis::rec_hw'] == i){
                options += '<option value="'+i+'" selected>'+data.result[i]+'</option>';
            }else{
                options += '<option value="'+i+'">'+data.result[i]+'</option>';
            }
        }
        $('.eqLogicAttr[data-l1key=configuration][data-l2key="jarvis::rec_hw"]').empty().append(options);
    }
});

   try{
     $('#bt_uploadMagicWordSnowboy').fileupload('destroy');
 }
 catch (e) {

 }

 $('#bt_uploadMagicWordSnowboy').fileupload({
    replaceFileInput: false,
    url: 'plugins/jarvis/core/ajax/jarvis.ajax.php?action=uploadMagicWordSnowboy&id=' + data.id +'&jeedom_token='+JEEDOM_AJAX_TOKEN,
    dataType: 'json',
    done: function (e, data) {
        if (data.result.state != 'ok') {
            $('#div_alert').showAlert({message: data.result.result, level: 'danger'});
            return;
        }
    }
});
}

