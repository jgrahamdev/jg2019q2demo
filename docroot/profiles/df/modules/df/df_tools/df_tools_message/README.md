DF Tools Message
----
This module uses Foundation for Emails and Inky, which allow users to write
email templates using a grid system in a similar way to Foundation for Sites.

# Installation

To compile the Twig templates used by this module, run:

1. npm install
1. gulp

And you should see templates updated from /inky_templates to /inky_templates/compiled.

# Compiling templates from other directories

If you want to compile templates for another directory, run:

* gulp --directory ~/Sites/my_site/modules/my_module

It's expected that the directory you pass will contain
inky_templates/_wrap.html.twig, which should contain the wrapping tags for your
templates.

See df_tools_message/inky_templates/_wrap.html.twig for reference.
