moodle-message_telegrambot
==========================

**Notificações do Moodle entregues por um bot do Telegram.**

.. note::

   Versão 0.1.1 (alfa).

Recursos
--------

* Entrega qualquer notificação do Moodle (fórum, tarefas, notas, avisos do ``local_alerthub``) no Telegram.
* Cada usuário escolhe o chat: a conversa com o bot, um grupo (id negativo) ou um canal (``@nome``).
* Assunto em negrito, texto e link da notificação; HTML escapado; corte no limite de 4.096 caracteres.
* O token do bot nunca aparece em registros nem em erros.
* Desligado por padrão: o administrador habilita só nos tipos de notificação que devem ir para o Telegram.

Requisitos
----------

* Moodle 4.5 ou 5.3 (LTS).
* Um bot do Telegram criado com o @BotFather; saída HTTPS para ``api.telegram.org``.

.. toctree::
   :maxdepth: 1

   installation
   configuration
   usage
