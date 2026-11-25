<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Strings of message_telegrambot (pt_br).
 *
 * @package    message_telegrambot
 * @copyright  2026 onwards Kelson da Costa Medeiros
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['chatid'] = 'Id do chat no Telegram';
$string['chatid_help'] = 'Número da sua conversa com o bot, de um grupo (negativo, como -1001234567890) ou o nome de um canal (@nome). Mande uma mensagem ao bot ou adicione-o ao grupo e peça o id ao administrador.';
$string['invalidchatid'] = 'Informe o id do chat: um número (negativo nos grupos, como -1001234567890) ou o nome de um canal (@nome).';
$string['notconfigured'] = 'O bot do Telegram ainda não está configurado neste site.';
$string['pluginname'] = 'Bot do Telegram';
$string['privacy:metadata:preference:chatid'] = 'O chat do Telegram para onde vão as notificações do usuário.';
$string['privacy:metadata:telegram'] = 'As notificações são enviadas ao Telegram pela Bot API.';
$string['privacy:metadata:telegram:chatid'] = 'O chat que recebe a notificação.';
$string['privacy:metadata:telegram:text'] = 'O assunto, o texto e o link da notificação.';
$string['testmessage'] = 'Enviar mensagem de teste ao Telegram';
$string['testmessage_default'] = 'Mensagem de teste de {$a}. Se você está lendo, o bot do Telegram do site funciona.';
$string['testmessage_failed'] = 'O Telegram não entregou a mensagem: {$a}';
$string['testmessage_intro'] = 'Envia uma mensagem a um chat do Telegram com o token salvo nas configurações. Use para conferir o token e o id do chat antes de ativar as notificações. O bot precisa estar no grupo, ou a pessoa precisa ter iniciado uma conversa com ele. O texto aceita Markdown simples: *negrito*, _itálico_, ~riscado~, código entre crases e [texto](https://...).';
$string['testmessage_link'] = '<a href="{$a}">Envie uma mensagem de teste</a> para conferir o token e o id de um chat.';
$string['testmessage_notoken'] = 'O token do bot ainda não foi configurado. <a href="{$a}">Configure-o</a> antes de enviar uma mensagem de teste.';
$string['testmessage_send'] = 'Enviar mensagem de teste';
$string['testmessage_sent'] = 'Mensagem enviada ao chat {$a}. Confira no Telegram.';
$string['testmessage_subject'] = 'Mensagem de teste: {$a}';
$string['testmessage_text'] = 'Texto';
$string['token'] = 'Token do bot';
$string['token_desc'] = 'Token fornecido pelo @BotFather na criação do bot. Nunca aparece em registros nem em mensagens de erro.';
