Adiomatique
===========

A WordPress plugin that builds upon custom fields of posts and pages, to provide some basic integrated event management.

This work is not as presentable/usable as I'd like, yet. It here mostly for code review and backups. 

Why
---

I didn't want any more monster plugins on an NGO's tiny homepage that load ~150kb js for facebook comments support, despite it being disabled in the options. Also, I needed a pet project to build, to balance out my job in a factory. The existing solutions were far removed from the habitual workflow of the users.


Limitations
-----------

Before you dive, heads up:

* This plugin doesn't support l10n, yet. The user interface language is hardcoded german.
* No timezone support. Local time is saved as a unix timestamp (so UTC). Since opening hours don't change with seasons, it should be fine.
* Security: input is only possible from admin-area. Thus, there is close to no server-side validation/sanitization. Then again, this plugin relies only on the "custom values" storage functions provided by WP.
* It's basically spaghetti out of a few hooks and filters. I might refactor it with OOD, but at less than 1k SLOC I have other things to do.
* Tests: coming up.

Requirements
------------
[https://github.com/fgelinas/timepicker](jQuery UI Timepicker by François Gélinas)

License
-------
[GPL-3.0](http://www.gnu.org/licenses/gpl-3.0.html)
