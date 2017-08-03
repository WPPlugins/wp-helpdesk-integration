jQuery(document).ready( function(){
});  
 


function TFA_Authkey_Save( auth_val)
{
    var TFA_authtoken = auth_val;
    jQuery.ajax({
        type: 'POST',
        url : ajaxurl,
        data : {
            action : 'smack_whi_TFA_auth_save',
            authtoken : TFA_authtoken
        } ,
        success: function(data){
        },
        error: function(errorThrown){
            console.log( data );
        }

    });
}
function validateCrmStuffFetched()
{
    document.getElementById('loading-image').style.display = "block";

    var  data_array = {
        'action'        : 'adminAjaxActionsForHelpDesk',
        'operation'     : 'NoFieldOperation',
        'doaction'      : 'CheckFetchedDetails',
    };

    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: data_array,
        success:function(data) {
            jQuery("#settingsavedmessage").css('display' , 'block');
            jQuery("#settingsavedmessage").html('Saved');
            jQuery("#settingsavedmessage").css('display','inline').fadeOut(3000);
            document.getElementById('loading-image').style.display = "none";
        },
        error: function(errorThrown){
            console.log(errorThrown);
        }
    });
}


function saveFormSettings( shortcodename )
{
    //var formtype = jQuery("select[name=formtype]").val();
    //var duplicate_handling = jQuery("input[type=radio][name=check_duplicate]:checked").val();
    //var assignedto = jQuery("select[name=assignedto]").val();
    var assignemail = jQuery("select[name=assignedto] option:selected").text();
    var errormessage = jQuery("input[name=errormessage]").val();
    var successmessage = jQuery("input[name=successmessage]").val();
    var enableurlredirection = jQuery("input[type=checkbox][name=enableurlredirection]").is(':checked');
    var redirecturl = jQuery("input[name=redirecturl]").val();
    var enablecaptcha = jQuery("input[type=checkbox][name=enablecaptcha]").is(':checked');
    var savedetails = '<br>FormType &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ' + formtype + '<br>' + 'Shortcode &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:  ' + shortcodename + '<br>' + 'Assignee&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: ' + assignemail   ;

    jQuery("select[name=assignedto]").change(function(){
        var option_selected = jQuery(this).find("option:selected").text();
    });

    var redirect = jQuery("#redirecturl").val();
   
    if( redirect.length > 0 )
    {
        var redir_postid = '<br>Redir Post-id &nbsp;:' + redirect;
    }

    document.getElementById('loading-image').style.display = "block";

    var  data_array = {
        'action'        : 'adminAjaxActionsForHelpDesk',
        'operation'	    : 'NoFieldOperation',
        'doaction'      : 'SaveFormSettings',
        'shortcode'     : shortcodename,
          'errormessage'  : errormessage,
        'successmessage': successmessage,
        'enableurlredirection' : enableurlredirection,
        'redirecturl'   : redirecturl,
        'enablecaptcha' : enablecaptcha,
           };

    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: data_array,
        success:function(data) {
            console.log(data);
            jQuery("#savedetails").css('display' , 'block');
            jQuery("#savedetails").html(savedetails);
            jQuery("#savedetails").css('display','inline').fadeOut(3800);

            jQuery("#url_post_id").css('display' , 'block');
            jQuery("#url_post_id").html(redir_postid);
            jQuery("#url_post_id").css('display','inline').fadeOut(3800);

            jQuery("#settingsavedmessage").css('display' , 'block');
            jQuery("#settingsavedmessage").html('saved succesfully');
            jQuery("#settingsavedmessage").css('display','inline').fadeOut(3000);
            document.getElementById('loading-image').style.display = "none";
            jQuery('#field_settings').click();
        },
        error: function(errorThrown){
            console.log(errorThrown);
        }
    });
}


function SmackActiveHelpDesk( thiselement )
{
    var x = document.getElementById("pluginselect").selectedIndex;
    var select = document.getElementsByTagName("option")[x].value;
    var old_crm_pro = jQuery( "#revert_old_crm_pro" ).val();
    var get_config = jQuery( "#get_config" ).val();
    if( get_config == 'no' )
    {
        var pluginselect_value;
        document.getElementById('loading-image').style.display = "block";
        for(var i = 0; i < pluginselect.length; i++){
            if(pluginselect[i].selected == true){
                pluginselect_value = pluginselect[i].value;
            }
        }
        var redirectURL=document.getElementById('plug_URL').value;
        var postdata = pluginselect_value;
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                'action'   : 'SmackHelpDeskChangeActivePortal',
                'postdata' : postdata,
            },
            success:function(data) {
                location.href=redirectURL+'&__module=Settings&__action=view';   //      Redirect to Plugin Settings page
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });
    }
    else
    {
        jQuery.confirm({
            title:'',
            content:'Switching to another Helpdesk will make your old short codes disabled.',
            confirmButtonClass: 'button-primary',
            cancelButtonClass: 'btn-danger',
            confirmButton: 'Confirm',
            cancelButton:'Cancel',
            keyboardEnabled: true,
            confirm: function(){
                var pluginselect_value;
                document.getElementById('loading-image').style.display = "block";
                for(var i = 0; i < pluginselect.length; i++){
                    if(pluginselect[i].selected == true){
                        pluginselect_value = pluginselect[i].value;
                    }
                }
                var redirectURL=document.getElementById('plug_URL').value;
                var postdata = pluginselect_value;
                jQuery.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        'action'   : 'SmackHelpDeskChangeActivePortal',
                        'postdata' : postdata,
                    },
                    success:function(data) {
                        location.href=redirectURL+'&__module=Settings&__action=view';   //      Redirect to Plugin Settings page
                    },
                    error: function(errorThrown){
                        console.log(errorThrown);
                    }
                });
            },
            cancel: function(){
                jQuery("#pluginselect").val( old_crm_pro );
            }
        })
    }
}




function gotoTopHelpDesk()
{
    jQuery(window).scrollTop(0);
}



function saveHelpDeskConfiguration( id ) {
    jQuery( "#Fieldnames" ).html("");
    jQuery( "#Fieldnames" ).hide();
    document.getElementById('loading-image').style.display = "block";
    var siteurl = jQuery( "#site_url" ).val();
    var active_plugin = jQuery( "#active_plugin" ).val();

    //Remove index.php from vtiger  and sugar URl

    var ticket_fields_tmp = 'smack_whi_' + active_plugin + '_ticket_fields-tmp'; 
    var tickets = "Tickets";
   
    var config_data = JSON.parse( "" || "{}");
    var items = jQuery("form :input").map(function(index, elm) {
        return {name: elm.name, type:elm.type, value: jQuery(elm).val()};
    });

    jQuery.each(items, function(i, d){
        if(d.value != '' && d.value != null)
            config_data[d.name] = d.value;
    });
    jQuery.ajax({
        type : 'POST',
        url  : ajaxurl,
        data :{
            'action' : 'SmackHelpDeskSaveConfiguration',
            'doaction': 'Saveandfetch',
            'posted_data' : config_data,
        },
        success:function( data )
        {
            console.log(data);
            var data = JSON.parse( data );
            if( data.error == 0 )
            {
                document.getElementById('loading-image').style.display = "none";
                document.getElementById('loading-sync').style.display = "block";
                jQuery("#save_config").html( data.display );
                SmackHelpDeskFetchFields(siteurl , active_plugin , tickets , ticket_fields_tmp , 'ticket' );

            }
            else if(data.error == 11 )
            {
                document.getElementById( 'loading-image').style.display = "none";
                jQuery("#save_config").html( data.display );
            }
            else
            {
                document.getElementById('loading-image').style.display = "none";
                jQuery("#save_config").html( data.display );
            }
        },
        error:function( errorThrown )
        {
            console.log( errorThrown );
        }

    } );


}

function SmackHelpDeskSelectAllFields(formid,module)
{
    var i;
    var data="";
    var form =document.getElementById(formid);
    var chkall = form.elements['selectall'];
    var chkBx_count = form.elements['no_of_rows'].value;

    if(chkall.checked == true){
        for (i=0;i<chkBx_count;i++){
            if(document.getElementById('select'+i).disabled == false)
                document.getElementById('select'+i).checked = true;
        }
    }else{
        for (i=0;i<chkBx_count;i++){
            if(document.getElementById('select'+i).disabled == false)
                document.getElementById('select'+i).checked = false;
        }
    }
}

function assignedUsers(siteurl, option, onAction, shortcode) {
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            'action'	 : 'adminAjaxActionsForHelpDesk',
            'doaction' 	 : 'FetchAssignedUsers',
            'siteurl'	 : siteurl,
            'option'	 : option,
            'onAction'	 : onAction,
            'shortcode'	 : shortcode,
        },
        success:function(data) {
            jQuery( "#Fieldnames" ).append( 'Users Synced' + "<br>" );
            document.getElementById('loading-sync').style.display = "none";
            location.reload();
        },
        error: function(errorThrown){
            console.log(errorThrown);
        }
    });
}

function SmackHelpDeskFetchFields (siteurl, portal_type, module, option, onAction, contactmodule, contact_fields_tmp, call_back) {
//Clear CSS
    var shortcode = '';
    if(onAction == 'onEditShortCode')
    {
        shortcode = jQuery('#shortcode').val();
    }

    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            'action'	 : 'adminAjaxActionsForHelpDesk',
            'doaction' 	 : 'FetchCrmFields',
            'siteurl'	 : siteurl,
            'module'	 : module,
            'portal_type': portal_type,
            'option'	 : option,
            'onAction'	 : onAction,
            'shortcode'	 : shortcode,
        },
        success:function(data) {
            jQuery( "#Fieldnames" ).show();
            jQuery( "#Fieldnames" ).append(module + ' fields Synced' + '<br>');
            console.log(call_back);
            var active_plugin = jQuery( "#active_plugin" ).val();
            assignedUsers( siteurl , active_plugin , 'options' );
            },
        error: function(errorThrown){
            console.log(errorThrown);
        }
    });
}

function applyChangesOnHelpDeskShortCode(siteurl, module, option, onAction)
{
    var portal_type;
    portal_type = document.getElementById("active_helpdesk").value;
    document.getElementById('loading-image').style.display = "block";

    var shortcode = '';
    if(onAction == 'onEditShortCode')
    {
        shortcode = jQuery('#shortcode').val();
    }

    var flag = true;

    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            'action'     : 'adminAjaxActionsForHelpDesk',
            'doaction'   : 'CheckformExits',
            'siteurl'    : siteurl,
            'module'     : module,
            'portal_type': portal_type,
            'option'     : option,
            'onAction'   : onAction,
            'shortcode'  : shortcode,
        },
        success:function(data) {
            document.getElementById('loading-image').style.display = "none";
            if(data == "Not synced")
            {
                alert("Must Fetch fields before Saving Settings");
                flag = false;
                return false;
            }
            else
            {
                return true;
            }
        },
        error: function(errorThrown){
            console.log(errorThrown);
        }
    });
    return flag;
}

//--------------------------------------------------------------------------------
//--------------------------------------------------------------------------------

/*Form building module JS*/
function enableredirecturl(id) {
    if(document.getElementById("enableurlredirection").checked == true) {
        document.getElementById("redirecturl").disabled = false;
    } else {
        document.getElementById("redirecturl").disabled = true;
    }
}
function enablesmackemail(id) {
    var smack_email_condition = jQuery( "#emailcondition" ).val();
    if( smack_email_condition == "none" )
    {
        jQuery("#email").prop( 'disabled' , true );
    }
    else
    {
        jQuery("#email").prop( 'disabled' , false );
    }
}

function enablesmackTFA(id) {
    if(document.getElementById("TFA_check").checked == true) {
        document.getElementById("TFA_authkey").disabled = false;
        jQuery( "#TFA_check" ).val( 'on' );
    } else {
        document.getElementById("TFA_authkey").disabled = true;
        jQuery( "#TFA_check" ).val('off');
    }
}

function debugmod(id) {
    if(document.getElementById("debugmode").checked == true) {
        jQuery( "#debugmode" ).val('on');
    } else {
        jQuery( "#debugmode" ).val('off');
    }
}
