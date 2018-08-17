<?php
namespace Swiftype;

class SwiftypeClient {

	private $api_key;
	private $host; // API Endpoint
	private $api_base_path;

	public function __construct($api_key = null, $host = null, $api_base_path = '/api/as/v1/') {

		$this->api_key = $api_key;
		$this->host = $host;
		$this->api_base_path = $api_base_path;

		if(!function_exists('curl_init')){
			throw new \Exception('Swiftype requires the CURL PHP extension.');
		}

		if(!function_exists('json_decode')){
  			throw new \Exception('Swiftype requires the JSON PHP extension.');
		}
	}

	public function engines() {
		return $this->get($this->engines_path());
	}

	public function engine($engine_id) {
		return $this->get($this->engine_path($engine_id));
	}

	public function create_engine($engine_id) {
		$engine = array('name' => $engine_id);

		return $this->post($this->engines_path(), array(), $engine);
	}

	public function delete_engine($engine_id) {
		return $this->delete($this->engine_path($engine_id));
	}

  // Retrieves all documents from an engine
	public function list_documents($engine_id) {
		return $this->get($this->documents_path($engine_id).'/list');
	}

  // Retrieves documents by ids
	public function documents($engine_id, $document_ids = array()) {
		return $this->get($this->documents_path($engine_id), array(), $document_ids, false);
	}

	public function create_documents($engine_id, $documents = array()) {
		return $this->post($this->documents_path($engine_id), array(), $documents, false);
	}

	public function update_documents($engine_id, $documents = array()) {
		return $this->post($this->documents_path($engine_id), array(), $documents, false);
	}

	public function create_or_update_documents($engine_id, $documents = array()) {
		return $this->post($this->documents_path($engine_id), array(), $documents, false);
	}

	public function delete_documents($engine_id, $document_ids = array()) {
		return $this->delete($this->documents_path($engine_id), array(), $document_ids, false);
	}

	public function search($engine_id, $query, $options = array()) {
		$query_string = array('query' => $query);
		$full_query = array_merge($query_string, $options);
    
		return $this->post($this->search_path($engine_id), array(), $full_query);
	}

	private function search_path($engine_id) {
		return 'engines/'.$engine_id.'/search';
	}
  
	private function engines_path() {
		return 'engines';
	}

	private function engine_path($engine_id) {
		return 'engines/'.$engine_id;
	}

	private function documents_path($engine_id) {
		return $this->engine_path($engine_id).'/documents';
	}

	private function get($path, $params = array(), $data = array(), $json_object=true) {
		return $this->request('GET', $path, $params, $data, $json_object);
	}

	private function post($path, $params = array(), $data = array(), $json_object=true) {
		return $this->request('POST', $path, $params, $data, $json_object);
	}

	private function delete($path, $params = array(), $data = array(), $json_object=true) {
		return $this->request('DELETE', $path, $params, $data, $json_object);
	}

	private function put($path, $params = array(), $data = array()) {
		return $this->request('PUT', $path, $params, $data);
	}

	private function request($method, $path, $params = array(), $data = array(), $json_object=true) {
    
		//Final URL
		$full_path = $this->host.$this->api_base_path.$path;

		// Throw an exception if the api key is not present
		if ($this->api_key !== null) {
			$params['auth_token'] = $this->api_key;
		} 
    else {
			throw new \Exception('Authorization requires an API key.');
		}

		//Build the query string
		$query = http_build_query($params);

		if ($query) {
			$full_path .= '?' . $query;
		}

		$request = curl_init($full_path);

		//Return the output instead of printing it
		curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($request, CURLOPT_FAILONERROR, true);
    
    if(!$json_object) {
      $body = ($data) ? json_encode($data) : ''; // create_document API request doesn't support JSON_FORCE_OBJECT format
    }
    else {
      $body = ($data) ? json_encode($data, JSON_FORCE_OBJECT) : '';
    }

		curl_setopt($request, CURLOPT_HTTPHEADER, array('Content-type: application/json'));

		if ($method === 'POST') {
			curl_setopt($request, CURLOPT_POST, true);
			curl_setopt($request, CURLOPT_POSTFIELDS, $body);
		}
    elseif ($method === 'GET') {
			curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'GET');
      curl_setopt($request, CURLOPT_POSTFIELDS, $body);
		}
    elseif ($method === 'DELETE') {
			curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'DELETE');
      curl_setopt($request, CURLOPT_POSTFIELDS, $body);
		}
    elseif ($method === 'PUT') {
			curl_setopt($request, CURLOPT_CUSTOMREQUEST, 'PUT');
			curl_setopt($request, CURLOPT_POSTFIELDS, $body);
		}

		$response = curl_exec($request);
		$error = curl_error($request);

		if ($error) {
	  	throw new \Exception("Sending message failed. Error: ". $error);
		}

		$http_status = (int)curl_getinfo($request,CURLINFO_HTTP_CODE);
		curl_close($request);

		// Any 2XX HTTP codes mean that the request worked
		if (intval(floor($http_status / 100)) === 2) {
      
			$final = json_decode($response);
			switch (json_last_error()) {
				case JSON_ERROR_DEPTH:
					$error = 'Maximum stack depth exceeded';
					break;
				case JSON_ERROR_CTRL_CHAR:
					$error = 'Unexpected control character found';
					break;
				case JSON_ERROR_SYNTAX:
					$error = 'Syntax error, malformed JSON';
					break;
				case JSON_ERROR_STATE_MISMATCH:
					$error = 'Underflow or the modes mismatch';
					break;
				case JSON_ERROR_UTF8:
					$error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
					break;
				case JSON_ERROR_NONE:
				default:
					$error = false;
					break;
			}

			if ($error === false) {
				// Request and response are OK
				if ($final) {
					return array(
						'status' => $http_status,
						'body' => $final
					);
				} 
        else {
					return array('status' => $http_status);
				}
			} 
      else {
				throw new \Exception('The JSON response could not be parsed: '.$error. '\n'.$response);
			}
		} 
    elseif ($http_status === 401) {
			throw new \Exception('Authorization required.');
		} 
    else {
	  		throw new \Exception("Couldn't send message, got response code: ". $http_status. " response: ".$response);
		}
	}
}
?>
