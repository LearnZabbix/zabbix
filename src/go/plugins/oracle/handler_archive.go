/*
** Zabbix
** Copyright (C) 2001-2024 Zabbix SIA
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

package oracle

import (
	"context"
	"fmt"

	"git.zabbix.com/ap/plugin-support/zbxerr"
)

func archiveHandler(ctx context.Context, conn OraClient, params map[string]string, _ ...string) (interface{}, error) {
	var archiveLogs string

	row, err := conn.QueryRow(ctx, getArchiveQuery(params["Destination"]))
	if err != nil {
		return nil, zbxerr.ErrorCannotFetchData.Wrap(err)
	}

	err = row.Scan(&archiveLogs)
	if err != nil {
		return nil, zbxerr.ErrorCannotFetchData.Wrap(err)
	}

	if archiveLogs == "" {
		archiveLogs = "[]"
	}

	return archiveLogs, nil
}

func getArchiveQuery(name string) string {
	var whereStr string
	if name != "" {
		whereStr = fmt.Sprintf(`AND DEST_NAME = '%s'`, name)
	}

	return fmt.Sprintf(`
	SELECT
		JSON_ARRAYAGG(
			JSON_OBJECT(d.DEST_NAME VALUE
				JSON_OBJECT(
					'status'       VALUE DECODE(d.STATUS, 'VALID', 3, 'DEFERRED', 2, 'ERROR', 1, 0),
					'log_sequence' VALUE d.LOG_SEQUENCE,
					'error'        VALUE NVL(TO_CHAR(d.ERROR), ' ')
				)
			) RETURNING CLOB 
		)		
	FROM
		V$ARCHIVE_DEST d,
		V$DATABASE db
	WHERE 
		d.STATUS != 'INACTIVE' 
		AND db.LOG_MODE = 'ARCHIVELOG'
		%s
`, whereStr)
}
