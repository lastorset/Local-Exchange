TODO: This is a work-in-progress collection of tips and advice. In time it should become a more comprehensive overview of the translation process.

Definitions
===========

String
------
A _string of text_ or _string_ is a single text to be translated. (Imagine letters dangling from a string you're holding up.) It's usually a sentence, but may be as long as a paragraph or as short as a single word.


While translating
=================

Translation hints
-----------------

Many strings have associated hints that explain the context or intended meaning. Always read them to make sure you've understood the string correctly. Some hints refer to special formatting that needs to be left as it is, but most of that should eventually be moved to this guide.

Special codes
-------------

Many strings contain special codes that must be left as they are, although you are expected to move them to the appropriate position in the translated string.

* `%s`, `%d` and similar constructs are words or numbers that are inserted by the software when displaying a web page. It could be a person's name, the number of karma points, etc. The hint should explain what it stands for, so you can place the code in the correct context when translating.
* `%1$s`, `%2$d` etc. are similar to `%s` and `%d`, but mainly used when there is more than one string to be replaced.
* `%(response_count)s` is also similar. Note especially that the 's' is not the plural.
* `{{ response_count }}` is another form.
* `<a>...</a>`, `<a1>...</a1>`, `<b>...</b>` etc. are placeholder HTML tags. They will be replaced by true HTML tags when displaying a web page. You will often see links presented as `<a1>` and `<a2>` even if they would be meaningless in actual HTML. `<a>` refers to a link, `<b>` makes text bold, `<i>` makes it italic.

Problematic cases
-----------------

If you find a string that you cannot translate sensibly under the constraints given here, please contact the developers. Usually this indicates that the string is defined too strictly, and the developers can add formatting codes to make them more flexible, or otherwise adjust the strings to make them more international.
