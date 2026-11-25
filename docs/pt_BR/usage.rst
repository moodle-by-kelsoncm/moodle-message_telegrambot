Uso
===

Cada usuário informa o id do chat nas preferências de notificação:

* conversa pessoal: o id do usuário, depois de mandar uma mensagem ao bot;
* grupo: o id negativo do grupo (``-100...``), depois de adicionar o bot a ele;
* canal: ``@nome``, com o bot como administrador do canal.

Para avisos de uma equipe, crie um usuário de serviço (autenticação "Sem login") com o id do grupo da equipe e use-o
como destinatário, por exemplo num canal do ``local_alerthub``.
