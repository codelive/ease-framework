<?php
/*
* Copyright 2014 Cloudward, Inc.
*
* This project is licensed under the terms of either the GNU General Public
* License Version 2 with Classpath Exception or the Common Development and
* Distribution License Version 1.0 (the "License").  See the LICENSE.txt
* file for details.
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/

/**
 * JSON-RPC Handler for use with the EASE Core to handle JSON-RPC requests
 *
 * @author Mike <mike@cloudward.com>
 */
class ease_json_rpc_handler {

	public $core;

	function __construct(&$core) {
		$this->core = $core;
	}

	function process() {
	}

}
