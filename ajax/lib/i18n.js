/**
 * Use Jed for JavaScript i18n.
 */

// TODO: Use a get_i18n script to get the correct language file
// TODO: Try jed-gettext-parser, so we can skip the JSON intermediate step
define(["jed", "includes/lang/nb_NO/LC_MESSAGES/nb"], function(Jed, nb) {
        return new Jed(nb);

        /* TODO: Allow calling Jed directly and having it delegate to Jed.gettext.
         *       Thus: define(['i18n'], function(_) { _('bla'); _.gettext('bla');}
         */
    }
);
