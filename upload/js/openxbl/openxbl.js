!function($, window, document, _undefined) 
{
    XenForo.OpenXBLProfile = 
    {
        profileTemplate: '',

        init: function()
        {
            $(document).bind('XFAjaxSuccess', $.context(this, 'loadProfile'));
            $(document).on('QuickReplyComplete', $.context(this, 'createProfile'));
        },

        loadProfile: function(e)
        {
            if (XenForo.hasTemplateHtml(e.ajaxData))
            {
                var $templateHtml = $(e.ajaxData.templateHtml),
                    $profile = $templateHtml.find('.openxblprofile');

                if ($profile.length)
                {
                    XenForo.OpenXBLProfile.profileTemplate = OpenXBLProfile.load($profile.attr('title'));
                }
            }
        },

        createProfile: function()
        {
            setTimeout(function() {
                $(document).find('.openxblprofile').last().empty().append(XenForo.OpenXBLProfile.profileTemplate);
            }, 1500);
        }
    }

    $(function()
    {
        XenForo.OpenXBLProfile.init();
    });
}
(jQuery, this, document);