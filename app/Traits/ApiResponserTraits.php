<?php

/**
 * Created by PhpStorm.
 * User: SST5
 * Date: 6/15/2019
 * Time: 1:26 PM
 */

namespace App\Traits;

use App\Models\ApiLog;
use http\Exception\BadMethodCallException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use PhpParser\Node\Scalar\String_;
use Symfony\Component\HttpFoundation\Response as ResponseHTTP;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Illuminate\Support\Str;


trait ApiResponserTraits
{
    /**
     * Default is (200).
     *
     * @var int
     */
    protected $statusCode = ResponseHTTP::HTTP_OK;
    /**
     * @var array
     */
    protected $headers = [];

    /**
     * Gets header data
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Sets header data
     *
     * @param array $headers
     *
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Gets status code
     *
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Sets status code
     *
     * @param mixed $statusCode
     *
     * @return mixed
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Responds with JSON, status code and headers.
     *
     * @param array $data
     *
     * @return JsonResponse
     */
    public function respond(array $data)
    {
        return new JsonResponse($data, $this->getStatusCode(), $this->getHeaders());
    }

    /**
     *  create API structure.
     *
     * @param boolean $success
     * @param null $payload
     * @param string $message
     * @param null $debug
     *
     * @return object
     */
    public function getResponseStructure($success = false, $payload = null, $message = '', $otherParam = array(), $debug = null)
    {
        $requestId = Str::uuid()->toString();
        if (isset($debug)) {
            $data = ['result' => $success, 'requestId' => $requestId, 'message' => $message, 'messageLBL' => $otherParam['messageLBL'] ?? null, 'payload' => $payload, 'debug' => $debug];
        } else {
            $data = ['result' => $success, 'requestId' => $requestId, 'message' => $message, 'messageLBL' => $otherParam['messageLBL'] ?? null, 'payload' => $payload];
        }

        if (config('constants.api_log_enabled')) {
            $api_log = array();
            $api_log['request_id'] = $requestId;
            $api_log['request_body'] = $this->formatLogRequest($_POST, 'request_body');
            $api_log['request_header'] = $this->formatLogRequest($_SERVER, 'request_header');
            $api_log['response'] = json_encode($data);
            $api_log['remote_address'] = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
            $api_log['request_port'] = $_SERVER['REMOTE_PORT'];
            $api_log['request_method'] = $_SERVER['REQUEST_METHOD'];
            $api_log['redirect_url'] = request()->getRequestUri();
            $api_log['http_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            ApiLog::create($api_log);
        }

        return $data;
    }

    /**
     * Responds with data
     *
     * @param mixed $data
     *
     * @return JsonResponse
     */
    public function respondWithData($data)
    {
        //            return [$data];
        $responseData = $this->getResponseStructure(true,  $data, '', []);
        //            return $responseData;
        $response = new JsonResponse($responseData, $this->getStatusCode(), $this->getHeaders());
        return $response;
    }

    /**
     * Use this for responding with messages.
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondWithMessage($message = "Ok", $otherParam = array())
    {
        $data = $this->getResponseStructure(true, null, $message, $otherParam);
        return $this->respond($data);
    }

    /**
     * Use this for responding with messages and payload.
     *
     * @param null $payload
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondWithMessageAndPayload($payload = null, $message = "Ok", $otherParam = array())
    {
        $data = $this->getResponseStructure(true, $payload, $message, $otherParam);
        $reponse = $this->respond($data);
        return $reponse;
    }

    /**
     * Responds with error
     *
     * @param string $message
     * @param null $e
     * @param null $data
     *
     * @return JsonResponse|null
     */
    public function respondWithError($message = "Error", $otherParam = array(), $e = null, $data = null)
    {
        $response = null;
        if (App::environment('local', 'staging') && isset($e)) {
            $debug_message = $e;
            $data = $this->getResponseStructure(false, $data, $message, $otherParam, $debug_message);
        } else {
            $data = $this->getResponseStructure(false, $data, $message, $otherParam);
        }
        $response = $this->respond($data);
        return $response;
    }

    /**
     * Use this to respond with a message (200).
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondOk($message = "Ok", $otherParam = array())
    {
        return $this->setStatusCode(ResponseHTTP::HTTP_OK)->respondWithMessage($message, $otherParam);
    }

    /**
     * Use this when a resource has been created (201).
     *
     * @param string $message
     *
     * @return mixed
     */
    public function respondCreated($message = "Created", $otherParam = array())
    {
        $reponse = $this->setStatusCode(ResponseHTTP::HTTP_CREATED)->respondWithMessage($message, $otherParam);
        return $reponse;
    }

    /**
     * Respond created with the payload
     *
     * @param null $payload
     * @param string $message
     *
     * @return mixed
     */
    public function respondCreatedWithPayload($payload = null, $message = "Created", $otherParam = array())
    {
        $reponse = $this->setStatusCode(ResponseHTTP::HTTP_CREATED)->respondWithMessageAndPayload($payload, $message, $otherParam);
        return $reponse;
    }

    /**
     * Use this when a resource has been updated (202).
     *
     * @param string $message
     *
     * @return mixed
     */
    public function respondUpdated($message = "Updated", $otherParam = array())
    {
        $reponse = $this->setStatusCode(ResponseHTTP::HTTP_ACCEPTED)->respondWithMessage($message, $otherParam);
        return $reponse;
    }

    /**
     * Respond is updated wih payload
     *
     * @param null $payload
     * @param string $message
     *
     * @return mixed
     */
    public function respondUpdatedWithPayload($payload = null, $message = "Updated", $otherParam = array())
    {
        $reponse = $this->setStatusCode(ResponseHTTP::HTTP_ACCEPTED)->respondWithMessageAndPayload($payload, $message, $otherParam);
        return $reponse;
    }

    /**
     * Use this when a resource has been deleted (202).
     *
     * @param string $message
     *
     * @return mixed
     */
    public function respondDeleted($message = "Deleted", $otherParam = array())
    {
        $reponse = $this->setStatusCode(ResponseHTTP::HTTP_ACCEPTED)->respondWithMessage($message, $otherParam);
        return $reponse;
    }

    /**
     * Use this when a resource has been deleted (202).
     *
     * @param null $payload
     * @param string $message
     *
     * @return mixed
     */
    public function respondDeletedWithPayload($payload = null, $message = "Deleted", $otherParam = array())
    {
        $reponse = $this->setStatusCode(ResponseHTTP::HTTP_ACCEPTED)->respondWithMessageAndPayload($payload, $message, $otherParam);
        return $reponse;
    }

    /**
     * Use this when the user needs to be authorized to do something (401).
     *
     * @param string $message
     *
     * @return mixed
     */
    public function respondUnauthorized($message = "Unauthorized", $otherParam = array())
    {
        $message = 'Given authorization token is invalid, please login again';
        return $this->setStatusCode(ResponseHTTP::HTTP_UNAUTHORIZED)->respondWithError($message, $otherParam);
    }

    /**
     * Use this when the user does not have permission to do something (403).
     *
     * @param string $message
     *
     * @return mixed
     */
    public function respondForbidden($message = "Forbidden", $otherParam = array())
    {
        return $this->setStatusCode(ResponseHTTP::HTTP_FORBIDDEN)->respondWithError($message, $otherParam);
    }

    /**
     * Use this when a resource is not found (404).
     *
     * @param string $message
     *
     * @return mixed
     */
    public function respondNotFound($message = "Not Found", $otherParam = array())
    {
        return $this->setStatusCode(ResponseHTTP::HTTP_NOT_FOUND)->respondWithError($message, $otherParam);
    }

    /**
     * Use this when a resource is not found (404).
     *
     * @param string $message
     *
     * @return mixed
     */
    public function respondMethodNotAllowed($message = "Method Not Allowed", $otherParam = array())
    {
        return $this->setStatusCode(ResponseHTTP::HTTP_METHOD_NOT_ALLOWED)->respondWithError($message, $otherParam);
    }

    /**
     * Use this when response validation error
     *
     * @param string $message
     * @param null $data
     *
     * @return mixed
     */
    public function respondValidationError($message = "Validation Error", $otherParam = array(), $data = null)
    {
        return $this->setStatusCode(ResponseHTTP::HTTP_UNPROCESSABLE_ENTITY)->respondWithError($message, $otherParam, null, $data);
    }

    /**
     * Use this for general server errors (500).
     *
     * @param string $message
     *
     * @return mixed
     */
    public function respondInternalError($message, $otherParam = array(), $e)
    {
        $message = $message ?: "Internal Error";
        return $this->setStatusCode(ResponseHTTP::HTTP_INTERNAL_SERVER_ERROR)->respondWithError($message, $otherParam, $e);
    }

    /**
     * Use this for general server errors (503).
     *
     * @param string $message
     *
     * @return mixed
     */
    public function respondServiceUnavailable($message, $otherParam = array(), $e)
    {
        $message = $message ?: "Service Unavailable";
        return $this->setStatusCode(ResponseHTTP::HTTP_SERVICE_UNAVAILABLE)->respondWithError($message, $otherParam, $e);
    }

    /**
     * Use this for general server errors (500).
     *
     * @param string $message
     *
     * @param string $status_code
     * @param \Exception $e
     * @return mixed
     */
    public function respondCustomError($message, $status_code, $otherParam = array(), $e)
    {
        $message = $message ?: "Internal Error";
        return $this->setStatusCode($status_code)->respondWithError($message, $otherParam, $e);
    }

    /**
     * Use this for HTTP not implemented errors (501).
     *
     * @param string $message
     *
     * @return mixed
     */
    public function respondNotImplemented($message = "Internal Error", $otherParam = array())
    {
        return $this->setStatusCode(ResponseHTTP::HTTP_NOT_IMPLEMENTED)->respondWithError($message, $otherParam);
    }

    /**
     * Use this for conflict of resource which already exists with unique key.
     *
     * @param string $message
     *
     * @return mixed
     */
    public function respondResourceConflict($message = "Resource Already Exists", $otherParam = array())
    {
        return $this->setStatusCode(ResponseHTTP::HTTP_CONFLICT)->respondWithError($message, $otherParam);
    }

    /**
     * Used when response resource conflict with data
     *
     * @param null $payload
     * @param string $message
     * @param int $responseCode
     *
     * @return mixed
     */
    public function respondResourceConflictWithData($payload = null, $message = "Resource Already Exists", $otherParam = array(), $responseCode = ResponseHTTP::HTTP_CONFLICT)
    {
        return $this->setStatusCode($responseCode)->respondWithMessageAndPayload($payload, $message, $otherParam);
    }

    /**
     * Response with file
     *
     * @param \Illuminate\Contracts\Filesystem\Filesystem $file
     * @param                                             $mime
     *
     * @return mixed
     */
    public function respondWithFile($file, $mime)
    {
        return (new \Illuminate\Http\Response($file, ResponseHTTP::HTTP_OK))->header('Content-Type', $mime);
    }

    /**
     * Response is no content
     *
     * @param $message
     *
     * @return mixed
     */
    public function respondNoContent($message, $otherParam = array())
    {
        return $this->setStatusCode(ResponseHTTP::HTTP_NO_CONTENT)->respondWithMessage($message, $otherParam);
    }

    /**
     * Use this for conflict of resource which already exists with unique key.
     *
     * @param string $message
     *
     * @return mixed
     */
    public function respondBadRequest($message = "Bad Request", $otherParam = array())
    {
        return $this->setStatusCode(ResponseHTTP::HTTP_BAD_REQUEST)->respondWithError($message, $otherParam);
    }

    /**
     * Use this for conflict of resource which already exists with unique key.
     *
     * @param string $message
     *
     * @return mixed
     */
    public function respondHTTPNotAcceptable($message = "HTTP Not Acceptable", $otherParam = array())
    {
        return $this->setStatusCode(ResponseHTTP::HTTP_NOT_ACCEPTABLE)->respondWithError($message, $otherParam);
    }

    /**
     * Use this for general server errors (400).
     *
     * @param string $message
     *
     * @return mixed
     */
    public function respondExceptionError($status, $message, $otherParam = array(), $status_code, $payload)
    {
        $responseData = $this->getResponseStructure($status, $payload, $message, $otherParam, null);
        $response = new JsonResponse($responseData, $status_code, $this->getHeaders());
        return $response;
    }

    /**
     * handle all type of exceptions
     *
     * @param \Exception $ex
     *
     * @return mixed|string
     */
    public function handleAndResponseException(\Exception $ex)
    {
        $response = '';
        switch (true) {
            case $ex instanceof \Illuminate\Database\Eloquent\ModelNotFoundException:
                $response = $this->respondNotFound('Record not found');
                break;
            case $ex instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException:
                $response = $this->respondNotFound("Not found");
                break;
            case $ex instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException:
                $response = $this->respondForbidden("Access denied");
                break;
            case $ex instanceof \Symfony\Component\HttpKernel\Exception\BadRequestHttpException:
                $response = $this->respondBadRequest("Bad request");
                break;
            case $ex instanceof BadMethodCallException:
                $response = $this->respondBadRequest("Bad method Call");
                break;
            case $ex instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException:
                $response = $this->respondForbidden("Method not found");
                break;
            case $ex instanceof \Illuminate\Database\QueryException:
                $response = $this->respondValidationError("Some thing went wrong with your query", [$ex->getMessage()]);
                break;
            case $ex instanceof \Illuminate\Http\Exceptions\HttpResponseException:
                $response = $this->respondInternalError("Something went wrong with our system", [$ex->getMessage()]);
                break;
            case $ex instanceof \Illuminate\Auth\AuthenticationException:
                $response = $this->respondUnauthorized("Unauthorized request");
                break;
            case $ex instanceof \Illuminate\Validation\ValidationException:
                $response = $this->respondValidationError("In valid request", [$ex->getMessage()]);
                break;
            case $ex instanceof NotAcceptableHttpException:
                $response = $this->respondUnauthorized("Unauthorized request");
                break;
            case $ex instanceof \Illuminate\Validation\UnauthorizedException:
                $response = $this->respondUnauthorized("Unauthorized request", [$ex->getMessage()]);
                break;
            case $ex instanceof \Exception:
                $response = $this->respondInternalError("Something went wrong with our system", [$ex->getMessage()]);
                break;
        }
        return $response;
    }


    protected function respondConflict($message = 'Conflict.') {
    return response()->json(['result' => false, 'message' => $message, 'payload' => null, 'requestId' => (string) \Str::uuid()], 409);
}

// protected function respondTooManyRequests($message = 'Too many requests.') {
//     return response()->json(['result' => false, 'message' => $message, 'payload' => null, 'requestId' => (string) \Str::uuid()], 429);
// }





    public function formatLogRequest($request, $type = '')
    {
        /**
         * @param $request => could be request_header or request_body
         * @param $type => use to identitfy the $request is either request_body or request_header
         *  this method is use to format $request body or header in a format like
         * eg if we have array('text'=>'hello world', 'number':123) it would format it like below
         * 'text':'hello world \n' 'number':123
         */

        $formated_request = '';
        if (empty($request)) {
            return $formated_request;
        } else {
            foreach ($request as $key => $value) {
                if ($type === 'request_header') {
                    // if(

                    //     in_array($key,['HTTP_ACCESSKEY','HTTP_ACCESSTOKEN','HTTP_KEY','HTTP_TOKEN'])
                    // )
                    {
                        $formated_request .= $key . ':' . $value . "\n";
                    }
                } elseif ($type === 'request_body') {
                    $formated_request .= $key . ':' . $value . "\n";
                } else {
                    $formated_request .= $key . ':' . $value . "\n";
                }
            }
        }
        return $formated_request;
    }
}
