<?php
/**
* Parses and contains all content written to the HTTP stream
* @package HttpRequest
* @license WTFPL
* @author Allan Thue Rehhoff
*/
namespace Http {
	class Response {
		private $request, $rawHeaders;
		private $responseHeaders = [];
		public $xmlErrors = [];

		public function __construct(\Http\Request $request) {
			$this->request = $request;

			if($this->request->response === false) {
				throw new BadRequestException(curl_errno($this->request->curl).": ".curl_error($this->request->curl), curl_errno($this->request->curl));
			}

			// And parse the headers for a client to use.
			rewind($this->request->headerHandle); 
			$this->rawHeaders = rtrim(stream_get_contents($this->request->headerHandle), "\r\n");
			$headersArray = array_filter(explode("\r\n", $this->rawHeaders));
			fclose($this->request->headerHandle);

			foreach($headersArray as $i => $line) {
				if ($i === 0) {
					$this->responseHeaders['Http-Code'] = $line;
				} else {
					$header = explode(': ', $line);

					if (isset($header[1])) {
						$this->responseHeaders[$header[0]] = trim($header[1]);
					} else {
						$this->responseHeaders[] = trim($header[0]);
					}
				}
			}

			if(is_resource($this->request->verbose)) {
				rewind($this->request->verbose); //@todo: Why do I need this, I'm still wondering...
				$verboseContent = stream_get_contents($this->request->verbose);

				$this->rawHeaders .= $verboseContent;
				$this->responseHeaders["verbosity"] = explode("\n", $verboseContent);
				unset($this->request->verbose);
			}
		}

		/**
		* Gives the raw response returned by remote server.
		* @since 1.2
		* @return (string)
		*/
		public function __toString() {
			return  $this->rawHeaders."\r\n".$this->request->response;
		}

		/**
		* Get cURL information regarding this request.
		* If index is given, returns its value, NULL if index is undefined.
		* Otherwise, returns an associative array of all available values.
		*
		* @param (string) $opt An index from curl_getinfo() returned array.
		* @see http://php.net/manual/en/function.curl-getinfo.php
		* @throws BadRequestException
		* @return (mixed)
		*/
		public function getInfo($opt = false) {
			if(empty($this->request->curlInfo)) {
				throw new BadRequestException("A cURL session has yet to be performed.");
			}

			if($opt !== false) {
				return isset($this->request->curlInfo[$opt]) ? $this->request->curlInfo[$opt] : null;
			}

			return $this->request->curlInfo;
		}

		/**
		* Returns the HTTP code represented by this reponse
		* @return (int)
		*/
		public function getCode() {
			return (int) $this->getInfo("http_code");
		}

		/**
		* Finds out whether a request was successful or not.
		* @return (bool)
		*/
		public function isSuccess() {
			return $this->getCode() < 400;
		}

		/**
		* Returns parsed header values.
		* If header is given returns that headers value.
		* Otherwise all response headers is returned.
		* 
		* @param (string) $header Name of the header for which to get the value
		* @return (mixed)
		*/
		public function getHeaders($header = false) {
			if($header !== false) {
				return isset($this->responseHeaders[$header]) ? $this->responseHeaders[$header] : null;
			}

			return $this->responseHeaders;
		}

		/**
		* Get cookies set by the remote server for the performed request, in case a cookiejar wasn't utilized.
		* @since 1.2
		* @param (string) $cookie Name of the cookie for which to retrieve details, null if it doesn't exist, ommit to get all cookies.
		* @return (array)
		*/
		public function getCookie($cookie = false) {
			if($cookie !== false) {
				return isset($this->responseHeaders["Set-Cookie"][$cookie]) ? $this->responseHeaders["Set-Cookie"][$cookie] : null;
			}

			return $this->responseHeaders["Set-Cookie"];
		}

		/**
		* Get the request response text without the headers.
		* @return (string)
		*/
		public function getResponse() {
			if($this->request->response === null) {
				throw new BadRequestException("Perform a request before accessing response data.");
			}

			return $this->request->response;
		}

		/**
		* Decodes and returns an object, assumes HTTP Response is JSON
		* @return (object)
		*/
		public function asObject() {
			return json_decode($this->getResponse());
		}

		/**
		* Decodes and returns an associative array, assumes the HTTP Response is JSON
		* @return (array)
		*/
		public function asArray() {
			return json_decode($this->getResponse(), true);
		}

		/**
		* Returns a SimpleXML object with containing the response content.
		* After calling any potential xml error will be available for inspection in the $xmlErrors property.
		* @param (bool) $useErrors Toggle xml errors supression. Please be advised that setting this to true will also clear any previous XML errors in the buffer.
		* @return (object)
		*/
		public function asXml($useErrors = false) {
			libxml_use_internal_errors($useErrors);
			$xml = simplexml_load_string($this->getResponse());
			if($useErrors == false) $this->xmlErrors = libxml_get_errors();
			return $xml;
		}
	}
}