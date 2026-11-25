Usage
=====

Each user sets the chat id in the notification preferences:

* personal chat: the user id, after sending a message to the bot;
* group: the negative id of the group (``-100...``), after adding the bot to it;
* channel: ``@name``, with the bot as an administrator of the channel.

For alerts to a team, create a service user (authentication "No login") with the id of the team group and use it as
the recipient, for example in a channel of ``local_alerthub``.

Formatting
----------

* Notifications keep the formatting of their HTML version: bold, italics, underline, strike, code, quotes and links.
  The subject goes in bold at the top and the link of the notification at the end.
* The test message accepts simple Markdown: ``*bold*`` or ``**bold**``, ``_italic_``, ``~strike~``, ```code``` and
  ``[text](https://...)``.
