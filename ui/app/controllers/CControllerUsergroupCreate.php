<?php
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


class CControllerUsergroupCreate extends CController {

	protected function checkInput() {
		$fields = [
			'name' =>						'required|not_empty|db usrgrp.name',
			'userids' =>					'array_db users.userid',
			'gui_access' =>					'db usrgrp.gui_access|in '.implode(',', [GROUP_GUI_ACCESS_SYSTEM, GROUP_GUI_ACCESS_INTERNAL, GROUP_GUI_ACCESS_LDAP, GROUP_GUI_ACCESS_DISABLED]),
			'users_status' =>				'db usrgrp.users_status|in '.GROUP_STATUS_ENABLED.','.GROUP_STATUS_DISABLED,
			'debug_mode' =>					'db usrgrp.debug_mode|in '.GROUP_DEBUG_MODE_ENABLED.','.GROUP_DEBUG_MODE_DISABLED,
			'userdirectoryid' =>			'db usrgrp.userdirectoryid',

			'ms_new_group_right' =>			'array',
			'new_group_right' =>			'array',
			'ms_new_templategroup_right' =>	'array',
			'new_templategroup_right' =>	'array',
			'ms_new_tag_filter' =>			'array',
			'new_tag_filter' =>				'array',

			'form_refresh' =>				'int32'
		];

		$ret = $this->validateInput($fields);

		if (!$ret) {
			switch ($this->getValidationError()) {
				case self::VALIDATION_ERROR:
					$response = new CControllerResponseRedirect((new CUrl('zabbix.php'))
						->setArgument('action', 'usergroup.edit')
						->getUrl()
					);
					$response->setFormData($this->getInputAll());
					CMessageHelper::setErrorTitle(_('Cannot add user group'));
					$this->setResponse($response);
					break;

				case self::VALIDATION_FATAL_ERROR:
					$this->setResponse(new CControllerResponseFatal());
					break;
			}
		}

		return $ret;
	}

	protected function checkPermissions() {
		return $this->checkAccess(CRoleHelper::UI_ADMINISTRATION_USER_GROUPS);
	}

	protected function doAction() {
		$user_group = [
			'users' => zbx_toObject($this->getInput('userids', []), 'userid'),
			'hostgroup_rights' => [],
			'templategroup_rights' => [],
			'tag_filters' => []
		];

		$this->getInputs($user_group, ['name', 'users_status', 'gui_access', 'debug_mode', 'userdirectoryid']);

		$input = $this->getInputAll();
		$groupIds = $input['ms_new_group_right']['groupids'] ?? [];
		$permissions = $input['new_group_right']['permission'] ?? [];

		foreach ($groupIds as $index => $group) {
			foreach ($group as $groupId) {
				$permission = $permissions[$index] ?? PERM_DENY;

				if ($groupId !== '0') {
					$user_group['hostgroup_rights'][] = [
						'id' => (string) $groupId,
						'permission' => $permission
					];
				}
			}
		}

		$template_groupIds = $input['ms_new_templategroup_right']['groupids'] ?? [];
		$template_permissions = $input['new_templategroup_right']['permission'] ?? [];

		foreach ($template_groupIds as $index => $group) {
			foreach ($group as $groupId) {
				$permission = $template_permissions[$index] ?? PERM_DENY;

				if ($groupId !== '0') {
					$user_group['templategroup_rights'][] = [
						'id' => (string) $groupId,
						'permission' => $permission
					];
				}
			}
		}

		$tag_filters_groupIds = $input['ms_new_tag_filter']['groupids'] ?? [];
		$tags = $input['new_tag_filter']['tag'] ?? [];
		$values = $input['new_tag_filter']['value'] ?? [];

		foreach ($tag_filters_groupIds as $index => $group) {
			foreach ($group as $groupId) {
				$tag = $tags[$index] ?? null;
				$value = $values[$index] ?? null;

				if ($groupId !== '0'&& $tag !== null && $value !== null) {
					$user_group['tag_filters'][] = [
						'groupid' => $groupId,
						'tag' => $tag,
						'value' => $value,
					];
				}
			}
		}

		$result = (bool) API::UserGroup()->create($user_group);

		if ($result) {
			$response = new CControllerResponseRedirect((new CUrl('zabbix.php'))
				->setArgument('action', 'usergroup.list')
				->setArgument('page', CPagerHelper::loadPage('usergroup.list', null))
			);
			$response->setFormData(['uncheck' => '1']);
			CMessageHelper::setSuccessTitle(_('User group added'));
		}
		else {
			$response = new CControllerResponseRedirect((new CUrl('zabbix.php'))
				->setArgument('action', 'usergroup.edit')
			);
			CMessageHelper::setErrorTitle(_('Cannot add user group'));
			$response->setFormData($this->getInputAll());
		}

		$this->setResponse($response);
	}
}
