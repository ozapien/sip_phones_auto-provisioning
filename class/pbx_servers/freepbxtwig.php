<?php

/*
 * Copyright (C) 2016 Omar Zapien <omar.zapien at Google mail>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once '../../vendor/autoload.php';

class FreePBXTWIG extends Twig_Extension {

    public function getName() {
        return 'FreePBXTWIG';
    }

    public function getGlobals() {
        return array(
            // BLACK LIST CODES
            'FREEPBX_BL_NUMBER_CODE' => '*30',
            'FREEPBX_BL_LAST_CALLER_CODE' => '*32',
            'FREEPBX_BL_REMOVE_NUM_CODE' => '*31',
            //CALLFORWARD
            'FREEPBX_CF_ALL_ACTIVATE_CODE' => '*72',
            'FREEPBX_CF_ALL_DEACTIVATE_CODE' => '*73',
            'FREEPBX_CF_ALL_PROMPT_ACTIVATE_CODE' => '*93',
            'FREEPBX_CF_ALL_PROMPT_DEACTIVATE_CODE' => '*74',
            'FREEPBX_CF_BUSY_ACTIVATE_CODE' => '*90',
            'FREEPBX_CF_BUSY_DEACTIVATE_CODE' => '*91',
            'FREEPBX_CF_BUSY_PROMPT_ACTIVATE_CODE' => '*94',
            'FREEPBX_CF_BUSY_PROMPT_DEACTIVATE_CODE' => '*92',
            'FREEPBX_CF_NANSWR_ACTIVATE_CODE' => '*52',
            'FREEPBX_CF_NANSWR_DEACTIVATE_CODE' => '*53',
            'FREEPBX_CF_NANSWR_PROMPT_ACTIVATE_CODE' => '*95',
            'FREEPBX_CF_TOGGLE_CODE' => '*96',
            // CALLWAITING
            'FREEPBX_CW_ACTIVATE_CODE' => '*70',
            'FREEPBX_CW_DEACTIVATE_CODE' => '*71',
            // CONFERENCE
            'FREEPBX_CONF_STATUS_CODE' => '*87',
            // CORE
            'FREEPBX_GROUP_PICKUP_CODE' => '*8',
            'FREEPBX_CHANSPY_CODE' => '555',
            'FREEPBX_DIRECT_PICKUP_CODE' => '**',
            'FREEPBX_IN_CALL_ATTENDED_TRANSFER_CODE' => '*2',
            'FREEPBX_IN_CALL_BLIND_TRANSFER_CODE' => '##',
            'FREEPBX_IN_CALL_DISCONNECT_CODE' => '**',
            'FREEPBX_IN_CALL_TOGGLE_RECORDING_CODE' => '*1',
            'FREEPBX_SIMULATE_IN_CALL_CODE' => '7777',
            'FREEPBX_USER_LOGOFF' => '*12',
            'FREEPBX_USER_LOGIN_CODE' => '*11',
            'FREEPBX_ZAP_BARGE_CODE' => '888',
            // DAY NIGHT
            'FREEPBX_DAY_NIGHT_TOGGLE_CODE' => '*28',
            // DND
            'FREEPBX_DND_ACTIVATE_CODE' => '*78',
            'FREEPBX_DND_DEACTIVATE_CODE' => '*79',
            'FREEPBX_DND_TOGGLE_CODE' => '*76',
            // FAX
            'FREEPBX_DIAL_FAX_CODE' => '666',
            //FIND ME / FOLLOW ME
            'FREEPBX_FOLLOWME_CODE' => '*21',
            // HOTEL WAKE UP
            'FREEPBX_WAKEUP_CODE' => '*68',
            // INFO SERVICES
            'FREEPBX_CALL_TRACE_CODE' => '*69',
            'FREEPBX_ECHO_CODE' => '*43',
            'FREEPBX_SPEAK_EXTENSION_CODE' => '*65',
            'FREEPBX_SPEAK_CLOCK_CODE' => '*60',
            // PAGING
            'FREEPBX_INTERCOM_PREFIX_CODE' => '*80',
            'FREEPBX_INTERCOM_ALLOW_CODE' => '*54',
            'FREEPBX_INTERCOM_DISALLOW_CODE' => '*55',
            // PARKING
            'FREEPBX_PARK_CODE' => '*88',
            'FREEPBX_PARK_PICKUP_CODE' => '*85',
            // PARK PRO
            'FREEPBX_PARK_PRO_CODE' => '*88',
            // PHONEBOOK
            'FREEPBX_PHONEBOOK_CODE' => '411',
            // QUEUE
            'FREEPBX_QUEUE_ALLOW_DYNAMIC_CODE' => '*45',
            'FREEPBX_QUEUE_COUNT_CODE' => '*47',
            'FREEPBX_QUEUE_PAUSE_TOGGLE_CODE' => '*46',
            // SPEED DIAL
            'FREEPBX_SPEEDDIAL_SET_CODE' => '*75',
            'FREEPBX_SPEEDDIAL_CODE' => '*0',
            // TIME CONDITIONS
            'FREEPBX_TIME_CONDITION_OVERRIDE_CODE' => '*27',
            // VOICEMAIL
            'FREEPBX_VOICE_MAIL_DIAL_CODE' => '*98',
            'FREEPBX_VOICE_MAIL_DIRECT_CODE' => '*',
            'FREEPBX_VOICE_MAIL_MY_OWN_CODE' => '*97',
        );
    }

}
