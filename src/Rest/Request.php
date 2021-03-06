<?php
namespace Rest;

/**
 * Holds the Request in a Server
 */
class Request
{

    private $rest;

    private $requestURI;
    private $URIParts;

    private $requestMethod;
    private $get;
    private $post;
    private $files;

    private $input;

    /**
     * Constructor of RestRequest
     * @param \Rest\Server $rest = null, Parent RestServer
     */
    public function __construct(Server $rest = null)
    {

        // Sets most of the parameters
        $this->rest = $rest;

        if (isset($_SERVER["REQUEST_METHOD"]))
        {
            $this->requestMethod = $_SERVER["REQUEST_METHOD"];
        }

        if (isset($_SERVER["REQUEST_URI"]))
        {
            $this->requestURI = $_SERVER["REQUEST_URI"];
        }
        $this->URIParts = explode("/", $this->requestURI);

        $this->get = $_GET ? $_GET : array();
        $this->post = $_POST ? $_POST : array();
        $this->files = $_FILES ? $_FILES : array();

    }

    /**
     * Return  Server used;
     * @return \Rest\Server
     */
    public function getRest()
    {
        return $this->rest;
    }

    /**
     * Returns if Request is GET
     * @return boolean
     */
    public function isGet()
    {
        if ($this->requestMethod == "GET")
        {
            return true;
        }
        return false;
    }

    /**
     * Returns if Request is POST
     * @return boolean
     */
    public function isPost()
    {
        if ($this->requestMethod == "POST")
        {
            return true;
        }
        return false;
    }

    /**
     * Return if Request is PUT
     * @return boolean
     */
    public function isPut()
    {
        if ($this->requestMethod == "PUT")
        {
            return true;
        }
        return false;
    }

    /**
     * Return true if Request is DELETE
     * @return boolean
     */
    public function isDelete()
    {
        if ($this->requestMethod == "DELETE")
        {
            return true;
        }
        return false;
    }


    /**
     * Get parameters sent with GET (url parameters)
     * @param mixed $k get[$key]
     * @return mixed
     */
    public function getGet($k = null)
    {
        if ($k == null)
        {
            return $this->get;
        }
        else if (isset($this->get[$k]))
        {
            return $this->get[$k];
        }

        return null;
    }

    /**
     * Return parameters sent on a POST
     * @param mixed $k post[$key]
     * @return mixed
     */
    public function getPost($k = null)
    {
        if ($k == null)
        {
            return $this->post;
        }
        else if (isset($this->post[$k]))
        {
            return $this->post[$k];
        }

        return null;
    }

    /**
     * Return FILES sent on a POSt
     * @param mixed $k file[$key]
     * @return mixed
     */
    public function getFiles($k = null)
    {
        if ($k == null)
        {
            return $this->files;
        }
        else if (isset($this->files[$k]))
        {
            return $this->files[$k];
        }

        return null;
    }

    /**
     * Return content sent with PUT
     * @param mixed $k
     * @return mixed
     */
    public function getPut($k = null)
    {
        //  creating global variable if other libraries,... need to access the PUT parameters
        global $_PUT;


        if ($_SERVER['REQUEST_METHOD'] == 'PUT')
        {
            //  $_PUT already set, returns it
            if ( isset($_PUT) )
            {
                return $_PUT;
            }

            //  not set, create it
            $_PUT = array();
            $putdata = $this->getInput();
            $exploded = explode('&', $putdata);
            foreach ($exploded as $pair)
            {
                $item = explode('=', $pair);
                if (count($item) == 2)
                {
                    $_PUT[urldecode($item[0])] = urldecode($item[1]);
                }
            }

            if ($k == null)
            {
                return $_PUT;
            }
            else if ( isset($_PUT[$k]) )
            {
                return $_PUT[$k];
            }
        }

        return null;
    }

    /**
     * Return content sent with PUT
     * @return mixed
     */
    public function getInput()
    {
        $this->input = file_get_contents('php://input');
        return $this->input;
    }

    /**
     * Return request BODY
     * @return string
     */
    public function getBody()
    {
        return $this->getInput();
    }

    /**
     * Return user sent on BASIC Authentication
     * @return string
     */
    public function getUser()
    {
        return $this->rest->getAuthenticator()->getUser();
    }

    /**
     * Return password sent on Basic Authentication
     * @return string
     */
    public function getPassword()
    {
        return $this->rest->getAuthenticator()->getPassword();
    }

    /**
     * Return Request Method(PUT, DELETE, OPTION, GET...)
     * @return string
     */
    public function getMethod()
    {
        return $this->requestMethod;
    }

    /**
     * Set request method
     * @param string $method
     * @return \Rest\Request
     */
    public function setMethod($method)
    {
        $this->requestMethod = $method;
        return $this;
    }

    /**
     * Return the URI requested
     * @return string
     */
    public function getRequestURI()
    {
        return $this->requestURI;
    }

    /**
     * alias to getURIPart
     * @param int $i part of the uri
     * @return string
     */
    public function getParameter($i)
    {
        return $this->getURIPart($i);
    }

    /**
     * Return part of the URL
     * @param int $i part of the uri
     * @return string
     */
    public function getURIpart($i)
    {
        if (is_string($i))
        {
            $map = $this->getRest()->getMatch();
            if ($map)
            {
                foreach ($map as $n => $name)
                {
                    if ($name == ":" . $i)
                    {
                        return $this->getURI($n);
                    }
                    else if ($name == ":?" . $i)
                    {
                        return $this->getURI($n);
                    }
                }
            }
            else
            {
                return null;
            }
        }
        else if (is_int($i) && isset($this->URIParts[$i]))
        {
            return $this->URIParts[$i];
        }

        return null;
    }

    /**
     * Return the URI or part of it
     * @param int $i part of the uri
     * @return string
     */
    public function getURI($i = null)
    {
        if ($i !== null) return $this->getURIpart($i);
        return $this->getRequestURI();
    }

    /**
     * Sets the URI to deal
     * @param string $uri
     * @return \Rest\Request
     */
    public function setURI($uri)
    {
        $this->requestURI = $uri;
        $this->URIParts = explode("/", $this->requestURI);
        return $this;
    }

    /**
     * Return the extension of the URI (if any)
     * @return string
     */
    public function getExtension()
    {
        $reg = array();
        preg_match('@\.([a-zA-Z0-9]{1,5})$@', $this->getURI(), $reg);
        if (isset($reg[1]))
        {
            return $reg[1];
        }
        return false;
    }

    /**
     * Return true if given mime is accepted
     * @param string $mime to check
     * @return boolean
     */
    public function acceptMime($mime)
    {
        if (($pos = strpos($_SERVER["HTTP_ACCEPT"], $mime)) !== false)
        {
            return true;
        }
        return false;
    }

}

?>
