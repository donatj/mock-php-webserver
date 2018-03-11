<?php

namespace donatj\MockWebServer;

use donatj\MockWebServer\Exceptions\RuntimeException;

use ReflectionClass;

class ResponseStack implements MultiResponseInterface {

	private $ref;

	/**
	 * @var \donatj\MockWebServer\ResponseInterface[]
	 */
	private $responses = [];

	/**
	 * @var \donatj\MockWebServer\ResponseInterface|null
	 */
	protected $currentResponse;

	/**
	 * @var \donatj\MockWebServer\ResponseInterface
	 */
	protected $pastEndResponse;

	/**
	 * ResponseStack constructor.
	 *
	 * Accepts a variable number of RequestInterface objects
	 */
	public function __construct() {
		$responses = func_get_args();
		$refBase   = '';
		foreach( $responses as $response ) {
			if( !$response instanceof ResponseInterface ) {
				throw new RuntimeException('invalid response given - must be an instance of ResponseInterface');
			}

			$this->responses[] = $response;

			$refBase .= $response->getRef();
		}

		$this->ref = md5($refBase);

		$this->currentResponse = reset($this->responses) ?: null;
		$this->pastEndResponse = new Response('Past the end of the ResponseStack', [], 404);
	}

    public static function create($data) {
        $responses = [];

        foreach ($data as $item) {
            $responses[] = Response::create($item);
        }

        $reflector = new ReflectionClass('donatj\MockWebServer\ResponseStack');
        $response = $reflector->newInstanceArgs($responses);

        return $response;
    }

	/**
	 * @return bool
	 */
	public function next() {
		array_shift($this->responses);
		$this->currentResponse = reset($this->responses) ?: null;

		return (bool)$this->currentResponse;
	}

	/**
	 * @inheritdoc
	 */
	public function getRef() {
		return $this->ref;
	}

	/**
	 * @inheritdoc
	 */
	public function getBody() {
		return $this->currentResponse ?
			$this->currentResponse->getBody() :
			$this->pastEndResponse->getBody();
	}

	/**
	 * @inheritdoc
	 */
	public function getHeaders() {
		return $this->currentResponse ?
			$this->currentResponse->getHeaders() :
			$this->pastEndResponse->getHeaders();
	}

	/**
	 * @inheritdoc
	 */
	public function getStatus() {
		return $this->currentResponse ?
			$this->currentResponse->getStatus() :
			$this->pastEndResponse->getStatus();
	}

	/**
	 * @return \donatj\MockWebServer\ResponseInterface
	 */
	public function getPastEndResponse() {
		return $this->pastEndResponse;
	}

	/**
	 * @param \donatj\MockWebServer\ResponseInterface $pastEndResponse
	 */
	public function setPastEndResponse( ResponseInterface $pastEndResponse ) {
		$this->pastEndResponse = $pastEndResponse;
	}
}
