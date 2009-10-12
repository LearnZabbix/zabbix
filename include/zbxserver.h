/*
** ZABBIX
** Copyright (C) 2000-2005 SIA Zabbix
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
**/

#ifndef ZABBIX_FUNCTIONS_H
#define ZABBIX_FUNCTIONS_H

#include "common.h"
#include "comms.h"
#include "db.h"
#include "dbcache.h"
#include "sysinfo.h"

#define MACRO_TYPE_TRIGGER_DESCRIPTION	0x01
#define MACRO_TYPE_MESSAGE_SUBJECT	0x02
#define MACRO_TYPE_MESSAGE_BODY		0x04
#define MACRO_TYPE_MESSAGE		0x06	/* (MACRO_TYPE_MESSAGE_SUBJECT | MACRO_TYPE_MESSAGE_BODY) */
#define MACRO_TYPE_TRIGGER_EXPRESSION	0x08
#define MACRO_TYPE_ITEM_KEY		0x10
#define MACRO_TYPE_HOST_IPMI_IP		0x20
#define MACRO_TYPE_FUNCTION_PARAMETER	0x40

int	evaluate_function(char *value, DB_ITEM *item, const char *function, char *parameters, time_t now);
void    update_triggers (zbx_uint64_t itemid);
void	update_functions(DB_ITEM *item, time_t now);
void	dc_add_history(zbx_uint64_t itemid, unsigned char value_type, AGENT_RESULT *value, int now,
		int timestamp, char *source, int severity, int logeventid, int lastlogsize);

int	substitute_simple_macros(DB_EVENT *event, DB_ACTION *action, DB_ITEM *item, DC_ITEM *dc_item,
		DB_ESCALATION *escalation, char **data, int macro_type, char *error, int maxerrlen);
void	substitute_macros(DB_EVENT *event, DB_ACTION *action, DB_ESCALATION *escalation, char **data);

int	evaluate_expression(int *result,char **expression, DB_TRIGGER *triggger, char *error, int maxerrlen);
#endif
