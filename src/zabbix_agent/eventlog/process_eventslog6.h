/*
** Zabbix
** Copyright (C) 2001-2023 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

#include "../metrics.h"
#include "../logfiles/logfiles.h"

#include "zbxcomms.h"
#include "zbxalgo.h"

/* Structures from winevt.h file */
typedef HANDLE EVT_HANDLE, *PEVT_HANDLE;
/* Structures from winevt.h file END */

int	initialize_eventlog6(const char *source, zbx_uint64_t *lastlogsize, zbx_uint64_t *FirstID,
		zbx_uint64_t *LastID, EVT_HANDLE *render_context, EVT_HANDLE *query, char **error);
int	finalize_eventlog6(EVT_HANDLE *render_context, EVT_HANDLE *query);
int	process_eventslog6(zbx_vector_addr_ptr_t *addrs, zbx_vector_ptr_t *agent2_result,
		const char *eventlog_name, EVT_HANDLE *render_context, EVT_HANDLE *query, zbx_uint64_t lastlogsize,
		zbx_uint64_t FirstID, zbx_uint64_t LastID, zbx_vector_expression_t *regexps, const char *pattern,
		const char *key_severity, const char *key_source, const char *key_logeventid, int rate,
		zbx_process_value_func_t process_value_cb, const zbx_config_tls_t *config_tls, int config_timeout,
		const char *config_source_ip, ZBX_ACTIVE_METRIC *metric, zbx_uint64_t *lastlogsize_sent, char **error);
