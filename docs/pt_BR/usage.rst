Uso
===

Cada usuário informa o id do chat nas preferências de notificação:

* conversa pessoal: o id do usuário, depois de mandar uma mensagem ao bot;
* grupo: o id negativo do grupo (``-100...``), depois de adicionar o bot a ele;
* canal: ``@nome``, com o bot como administrador do canal.

Para avisos de uma equipe, crie um usuário de serviço (autenticação "Sem login") com o id do grupo da equipe e use-o
como destinatário, por exemplo num canal do ``local_alerthub``.

Formatação
----------

* As notificações mantêm a formatação da versão em HTML: negrito, itálico, sublinhado, riscado, código, citações e
  links. O assunto vai em negrito no topo e o link da notificação no final.
* A mensagem de teste aceita Markdown simples: ``*negrito*`` ou ``**negrito**``, ``_itálico_``, ``~riscado~``,
  ```código``` e ``[texto](https://...)``.
