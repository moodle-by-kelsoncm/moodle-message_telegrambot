moodle-message_telegrambot
==========================

**Moodle notifications delivered by a Telegram bot.**

.. note::

   Version 0.1.1 (alpha).

Features
--------

* Delivers any Moodle notification (forum posts, assignments, grades, alerts of ``local_alerthub``) to Telegram.
* Each user chooses the chat: the chat with the bot, a group (negative id) or a channel (``@name``).
* Bold subject, text and link of the notification; HTML escaped; cut to the 4096 characters limit.
* The bot token is never shown in logs or errors.
* Disabled by default: the administrator enables it only for the notification types that should go to Telegram.

Requirements
------------

* Moodle 4.5 or 5.3 (LTS).
* A Telegram bot created with @BotFather; outgoing HTTPS to ``api.telegram.org``.

.. toctree::
   :maxdepth: 1

   installation
   configuration
   usage
