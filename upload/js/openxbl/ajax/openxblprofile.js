function OpenXBLProfile() {
    // path/file config
    var basePath = 'js/openxbl/';
    var scriptFile = 'ajax/openxblprofile.js';
    var configFile = 'ajax/openxblprofile.xml';
    var proxyFile = 'jsonproxy.php';
    
    // language config
    var lang = "english";
    var langLocal = "english";
    var langData = {
        english : {
            loading : "Loading...",
            no_profile : "This user has not yet set up their Xbox Profile.",
            private_profile : "This profile is private.",
            invalid_data : "Invalid profile data."
        }
    };
    
    // misc config
    var loadLock = false;
    var configLoaded = false;
    var configData;

    // profile data
    var profiles = [];
    var profileCache = {};
    
    // template data
    var profileTpl;
    var loadingTpl;
    var errorTpl;

    this.init = function() {        
        
        // load xml config
        jQuery.ajax({
            type: 'GET',
            global: false,
            url: basePath + configFile,
            dataType: 'xml',
            success: function(request, status) {
                configData = $(request);
                loadConfig();
            }
        });
    };
    
    this.refresh = function() {
        // make sure we already got a loaded config
        // and no pending profile loads
        if (!configLoaded || loadLock) {
            return;
        }
        
        // lock loading
        loadLock = true;
        
        // select profile placeholders
        profiles = $('.openxblprofile[title]');
        
        // are there any profiles to build?
        if (profiles.length === 0) {
            return;
        }

        // store profile id for later usage
        profiles.each(function() {
            var profile = $(this);
            profile.data('profileID', $.trim(profile.attr('title')));
            profile.removeAttr('title');
        });

        // replace placeholders with loading template and make them visible
        profiles.empty().append(loadingTpl);
        
        // load profiles
        buildProfiles();
    };
    
    this.load = function(profileID) {
        // make sure we already got a loaded config
        // and no pending profile loads
        if (!configLoaded || loadLock) {
            return;
        }
        
        // create profile base
        profile = $('<div class="openxblprofile"></div>');
        
        // add loading template
        profile.append(loadingTpl);
        
        // load json data
        jQuery.ajax({
            type: 'GET',
            global: false,
            url: getJSONProxyURL(profileID),
            dataType: 'json',
            success: function(request, status) {
                // build profile and replace placeholder with profile
                var player = request;
                profile.empty().append(createProfile(player));
            }
        });
        
        return profile;
    };
    
    this.isLocked = function() {
        return loadLock;
    };
    
    function getXMLProxyURL(profileID) {
        return basePath + proxyFile + '?id=' + escape(profileID) + '&lang=' + escape(lang);
    }
    
    function getJSONProxyURL(friendQueryString) {
        return basePath + proxyFile + '?fullprofile=1' + '&openxblids=' + friendQueryString;
    }
    
    function getConfigString(name) {
        return configData.find('vars > var[name="' + name + '"]').text();
    }
    
    function getConfigBool(name) {
        return getConfigString(name).toLowerCase() == 'true';
    }
    
    function loadConfig() {
        lang = getConfigString('language');
        langLocal = lang;
        
        // fall back to english if no translation is available for the selected language in OpenXBLProfile
        if (langData[langLocal] == null) {
            langLocal = "english";
        }
        
        // load templates
        profileTpl = $(configData.find('templates > profile').text());
        loadingTpl = $(jQuery.parseHTML(configData.find('templates > loading').text()));
        errorTpl   = $(jQuery.parseHTML(configData.find('templates > error').text()));
        
        // set localization strings
        loadingTpl.append(langData[langLocal].loading);
        
        // we can now unlock the refreshing function
        configLoaded = true;
        
        // start loading profiles
        OpenXBLProfile.refresh();
    }

    function buildProfiles() {
        var openxblMaxProfiles = 999;
        var finishedOpenXBLIDs = [];
        var j = 0, friendQueryString = "";
        
        var uniqueProfiles = $(profiles).length;
        for(var i = 0; i < $(profiles).length; i++)
        {
            var hasAddedOpenXBLID = false;
            if (typeof profileCache[$(profiles[i]).data('profileID')] === "undefined") 
            {

                    if(j > 0)
                    {
                        friendQueryString = friendQueryString + ',';
                    }
                    friendQueryString = friendQueryString + $(profiles[i]).data('profileID');

                    finishedOpenXBLIDs[$(profiles[i]).data('profileID')] = true;
                    hasAddedOpenXBLID = true;
                    j++;
                        
                    if (j == openxblMaxProfiles || j == uniqueProfiles)
                    {

                        setTimeout(function(){
                        jQuery.ajax({
                            global: false,
                            type: 'GET',
                            url: getJSONProxyURL(friendQueryString),
                            dataType: 'json',
                            cache: true,
                            success: function(data, status, request) {
                  
                                if(data){
                                    $(data).each( function (index){

                                        var openxblID = $(this)[0].xuid;

                                        profileCache[openxblID] = createProfile($(this)[0]);

                                        for(var k = 0; k < $(profiles).length; k++)
                                        {
                                            if($(profiles[k]).data('profileID') == openxblID)
                                            {
                                                $(profiles[k]).html(profileCache[openxblID].html());
                                            }
                                        }

                                    });
                                    createEvents();
                                }
                            }
                        });
                        }, 10);
                        j = 0;
                    }
            }
            else
            {
                for(var k = 0; k < $(profiles).length; k++)
                {
                    if($(profiles[k]).data('profileID') == openxblID)
                    {
                        $(profiles[k]).append(profileCache[openxblID]);
                    }
                }
            }
        }
        
        loadLock = false;
        //Need to parse non-existence errors here.
    }

    function createProfile(profileData) {

        var profile = profileTpl.clone();
        
        profile.find('.sp-bg-game-img').css('border','1px #' + profileData.preferredColor.primaryColor + ' solid');
        profile.find('.sp-avatar img').attr('src', profileData.displayPicRaw);
        profile.find('.sp-gamertag').append(profileData.gamertag);
        profile.find('.sp-activity').text(profileData.presenceState);
        profile.find('.sp-gamerscore').text(profileData.gamerScore);
        /*
            .gamerScore
            .gamertag
            .broadcast[0]
            .multiplayerSummary > .InMultiplayerSession  .InParty
            .preferredColor > .primaryColor .secondaryColor .tertiaryColor
            .presenseText .presenceState
        */

        return profile;
    }
    
    function createEvents() {
        // add events for menu
        $('.sp-handle').unbind('click').click(function(e) {
            $(this).siblings('.sp-content').animate({'width': 'toggle'}, 200, 'linear');
            e.stopPropagation();
        });
    }

    function createError(message) {
        var errorTmp = errorTpl.clone();
        errorTmp.append(message);   
        return errorTmp;
    }
}

$(document).ready(function() {
    OpenXBLProfile = new OpenXBLProfile();
    OpenXBLProfile.init();
});