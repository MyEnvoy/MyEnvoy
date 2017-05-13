<?php

namespace Thirdparty;

use Exception;
use DOMDocument;
use Auth_SASL;
use Auth_SASL_Common;

/**
 * XMPP Prebind for PHP
 *
 * @copyright 2011 Amiado Group AG
 * @author Michael Weibel <michael.weibel@amiadogroup.com>
 * @license MIT
 * 
 * Adaptions by MyEnvoy devs
 */
/**
 * PEAR Auth_SASL
 */
require __DIR__ . '/auth/SASL.php';

/**
 * XMPP Library for connecting to jabber server & receiving sid and rid
 */
class XmppPrebind {

    const XMLNS_BODY = 'http://jabber.org/protocol/httpbind';
    const XMLNS_BOSH = 'urn:xmpp:xbosh';
    const XMLNS_CLIENT = 'jabber:client';
    const XMLNS_SESSION = 'urn:ietf:params:xml:ns:xmpp-session';
    const XMLNS_BIND = 'urn:ietf:params:xml:ns:xmpp-bind';
    const XMLNS_SASL = 'urn:ietf:params:xml:ns:xmpp-sasl';
    const XMLNS_VCARD = 'vcard-temp';
    const XML_LANG = 'en';
    const CONTENT_TYPE = 'text/xml charset=utf-8';
    const ENCRYPTION_PLAIN = 'PLAIN';
    const ENCRYPTION_DIGEST_MD5 = 'DIGEST-MD5';
    const ENCRYPTION_CRAM_MD5 = 'CRAM-MD5';
    const SERVICE_NAME = 'xmpp';

    protected $jabberHost = '';
    protected $boshUri = '';
    protected $resource = '';
    protected $encryption = self::ENCRYPTION_DIGEST_MD5;
    protected $jid = '';
    protected $password = '';
    protected $rid = '';
    protected $sid = '';
    protected $doSession = false;
    protected $doBind = false;
    // the Bosh attributes for use in a client using this prebound session
    protected $wait;
    protected $requests;
    protected $ver;
    protected $polling;
    protected $inactivity;
    protected $hold;
    protected $to;
    protected $ack;
    protected $accept;
    protected $maxpause;

    /**
     * Session creation response
     *
     * @var DOMDocument
     */
    public $response;

    /**
     * Create a new XmppPrebind Object with the required params
     *
     * @param string $jabberHost Jabber Server Host
     * @param string $boshUri    Full URI to the http-bind
     * @param string $resource   Resource identifier
     */
    public function __construct($jabberHost, $boshUri, $resource) {
        $this->jabberHost = $jabberHost;
        $this->boshUri = $boshUri;
        $this->resource = $resource;

        /*
         * The client MUST generate a large, random, positive integer for the initial 'rid' (see Security Considerations)
         * and then increment that value by one for each subsequent request. The client MUST take care to choose an
         * initial 'rid' that will never be incremented above 9007199254740991 [21] within the session.
         * In practice, a session would have to be extraordinarily long (or involve the exchange of an extraordinary
         * number of packets) to exceed the defined limit.
         *
         * @link http://xmpp.org/extensions/xep-0124.html#rids
         */
        if (function_exists('mt_rand')) {
            $this->rid = mt_rand(1000000000, 10000000000);
        } else {
            $this->rid = rand(1000000000, 10000000000);
        }
    }

    /**
     * connect to the jabber server with the supplied username & password
     *
     * @param string $username Username without jabber host
     * @param string $password Password
     */
    public function connect($username, $password) {
        $this->jid = $username . '@' . $this->jabberHost;

        if ($this->resource) {
            $this->jid .= '/' . $this->resource;
        }

        $this->password = $password;

        $response = $this->sendInitialConnection();
        if (empty($response)) {
            throw new XmppPrebindConnectionException("No response from server.");
        }

        $body = self::getBodyFromXml($response);
        if (empty($body))
            throw new XmppPrebindConnectionException("No body could be found in response from server.");
        $this->sid = $body->getAttribute('sid');

        // set the Bosh Attributes
        $this->wait = $body->getAttribute('wait');
        $this->requests = $body->getAttribute('requests');
        $this->ver = $body->getAttribute('ver');
        $this->polling = $body->getAttribute('polling');
        $this->inactivity = $body->getAttribute('inactivity');
        $this->hold = $body->getAttribute('hold');
        $this->to = $body->getAttribute('to');
        $this->accept = $body->getAttribute('accept');
        $this->maxpause = $body->getAttribute('maxpause');

        if (empty($body->firstChild) || empty($body->firstChild->firstChild)) {
            throw new XmppPrebindConnectionException("Child not found in response from server.");
        }

        // Assign session creation response
        $this->response = $body;
    }

    /**
     * Try to authenticate
     *
     * @throws XmppPrebindException if invalid login
     * @return bool
     */
    public function auth() {
        $auth = Auth_SASL::factory($this->encryption);

        $authXml = $this->sendChallengeAndBuildDigestMd5Auth($auth);
        $response = $this->send($authXml);

        $body = self::getBodyFromXml($response);

        if (!$body->hasChildNodes() || $body->firstChild->nodeName !== 'success') {
            throw new XmppPrebindException("Invalid login");
        }

        $this->sendRestart();
        $this->sendBindIfRequired();
        $this->sendSessionIfRequired();

        return true;
    }

    /**
     * Get BOSH parameters to properly setup the BOSH client
     *
     * @return array
     */
    public function getBoshInfo() {
        return array(
            'wait' => $this->wait,
            'requests' => $this->requests,
            'ver' => $this->ver,
            'polling' => $this->polling,
            'inactivity' => $this->inactivity,
            'hold' => $this->hold,
            'to' => $this->to,
            'ack' => $this->ack,
            'accept' => $this->accept,
            'maxpause' => $this->maxpause,
        );
    }

    /**
     * Get jid, sid and rid for attaching
     *
     * @return array
     */
    public function getSessionInfo() {
        return array('jid' => $this->jid, 'sid' => $this->sid, 'rid' => $this->rid);
    }

    /**
     * Send xmpp restart message after successful auth
     *
     * @return string Response
     */
    protected function sendRestart() {
        $domDocument = $this->buildBody();
        $body = self::getBodyFromDomDocument($domDocument);
        $body->appendChild(self::getNewTextAttribute($domDocument, 'to', $this->jabberHost));
        $body->appendChild(self::getNewTextAttribute($domDocument, 'xmlns:xmpp', self::XMLNS_BOSH));
        $body->appendChild(self::getNewTextAttribute($domDocument, 'xmpp:restart', 'true'));

        $restartResponse = $this->send($domDocument->saveXML());

        $restartBody = self::getBodyFromXml($restartResponse);
        foreach ($restartBody->childNodes as $bodyChildNodes) {
            if ($bodyChildNodes->nodeName === 'stream:features') {
                foreach ($bodyChildNodes->childNodes as $streamFeatures) {
                    if ($streamFeatures->nodeName === 'bind') {
                        $this->doBind = true;
                    } elseif ($streamFeatures->nodeName === 'session') {
                        $this->doSession = true;
                    }
                }
            }
        }

        return $restartResponse;
    }

    /**
     * Send xmpp bind message after restart
     *
     * @return string Response
     */
    protected function sendBindIfRequired() {
        if ($this->doBind) {
            $domDocument = $this->buildBody();
            $body = self::getBodyFromDomDocument($domDocument);

            $iq = $domDocument->createElement('iq');
            $iq->appendChild(self::getNewTextAttribute($domDocument, 'xmlns', self::XMLNS_CLIENT));
            $iq->appendChild(self::getNewTextAttribute($domDocument, 'type', 'set'));
            $iq->appendChild(self::getNewTextAttribute($domDocument, 'id', 'bind_' . rand()));

            $bind = $domDocument->createElement('bind');
            $bind->appendChild(self::getNewTextAttribute($domDocument, 'xmlns', self::XMLNS_BIND));

            $resource = $domDocument->createElement('resource');
            $resource->appendChild($domDocument->createTextNode($this->resource));

            $bind->appendChild($resource);
            $iq->appendChild($bind);
            $body->appendChild($iq);

            return $this->send($domDocument->saveXML());
        }
        return false;
    }

    /**
     * Send session if there's a session node in the restart response (within stream:features)
     */
    protected function sendSessionIfRequired() {
        if ($this->doSession) {
            $domDocument = $this->buildBody();
            $body = self::getBodyFromDomDocument($domDocument);

            $iq = $domDocument->createElement('iq');
            $iq->appendChild(self::getNewTextAttribute($domDocument, 'xmlns', self::XMLNS_CLIENT));
            $iq->appendChild(self::getNewTextAttribute($domDocument, 'type', 'set'));
            $iq->appendChild(self::getNewTextAttribute($domDocument, 'id', 'session_auth_' . rand()));

            $session = $domDocument->createElement('session');
            $session->appendChild(self::getNewTextAttribute($domDocument, 'xmlns', self::XMLNS_SESSION));

            $iq->appendChild($session);
            $body->appendChild($iq);

            return $this->send($domDocument->saveXML());
        }
        return false;
    }

    /**
     * Send initial connection string
     *
     * @param string $route
     * @return string Response
     */
    protected function sendInitialConnection() {
        $domDocument = $this->buildBody();
        $body = self::getBodyFromDomDocument($domDocument);

        $waitTime = 60;

        $body->appendChild(self::getNewTextAttribute($domDocument, 'hold', '1'));
        $body->appendChild(self::getNewTextAttribute($domDocument, 'to', $this->jabberHost));
        $body->appendChild(self::getNewTextAttribute($domDocument, 'xmlns:xmpp', self::XMLNS_BOSH));
        $body->appendChild(self::getNewTextAttribute($domDocument, 'xmpp:version', '1.0'));
        $body->appendChild(self::getNewTextAttribute($domDocument, 'wait', $waitTime));

        return $this->send($domDocument->saveXML());
    }

    /**
     * Send challenge request
     *
     * @return string Challenge
     */
    protected function sendChallenge() {
        $domDocument = $this->buildBody();
        $body = self::getBodyFromDomDocument($domDocument);

        $auth = $domDocument->createElement('auth');
        $auth->appendChild(self::getNewTextAttribute($domDocument, 'xmlns', self::XMLNS_SASL));
        $auth->appendChild(self::getNewTextAttribute($domDocument, 'mechanism', $this->encryption));
        $body->appendChild($auth);

        $response = $this->send($domDocument->saveXML());

        $body = $this->getBodyFromXml($response);
        $challenge = base64_decode($body->firstChild->nodeValue);

        return $challenge;
    }

    /**
     * Send challenge request and build DIGEST-MD5 auth string
     *
     * @param Auth_SASL_Common $auth
     * @return string Auth XML to send
     */
    protected function sendChallengeAndBuildDigestMd5Auth(Auth_SASL_Common $auth) {
        $challenge = $this->sendChallenge();

        $authString = $auth->getResponse(self::getNodeFromJid($this->jid), $this->password, $challenge, $this->jabberHost, self::SERVICE_NAME);

        $authString = base64_encode($authString);

        $domDocument = $this->buildBody();
        $body = self::getBodyFromDomDocument($domDocument);

        $response = $domDocument->createElement('response');
        $response->appendChild(self::getNewTextAttribute($domDocument, 'xmlns', self::XMLNS_SASL));
        $response->appendChild($domDocument->createTextNode($authString));

        $body->appendChild($response);


        $challengeResponse = $this->send($domDocument->saveXML());

        return $this->replyToChallengeResponse($challengeResponse);
    }

    /**
     * CRAM-MD5 and DIGEST-MD5 reply with an additional challenge response which must be replied to.
     * After this additional reply, the server should reply with "success".
     */
    protected function replyToChallengeResponse($challengeResponse) {
        $body = self::getBodyFromXml($challengeResponse);
        $challenge = base64_decode((string) $body->firstChild->nodeValue);
        if (strpos($challenge, 'rspauth') === false) {
            throw new XmppPrebindConnectionException('Invalid challenge response received');
        }

        $domDocument = $this->buildBody();
        $body = self::getBodyFromDomDocument($domDocument);
        $response = $domDocument->createElement('response');
        $response->appendChild(self::getNewTextAttribute($domDocument, 'xmlns', self::XMLNS_SASL));

        $body->appendChild($response);

        return $domDocument->saveXML();
    }

    /**
     * Send XML via CURL
     *
     * @param string $xml
     * @return string Response
     */
    protected function send($xml) {
        $ch = curl_init($this->boshUri);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $header = array('Content-Type: ' . self::CONTENT_TYPE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);

        // Check if curl failed to get response
        if ($response === false) {
            throw new XmppPrebindConnectionException("Cannot connect to service");
        }

        curl_close($ch);

        return $response;
    }

    /**
     * Build DOMDocument with standard xmpp body child node.
     *
     * @return DOMDocument
     */
    protected function buildBody() {
        $xml = new DOMDocument('1.0', 'UTF-8');

        $body = $xml->createElement('body');
        $xml->appendChild($body);

        $body->appendChild(self::getNewTextAttribute($xml, 'xmlns', self::XMLNS_BODY));
        $body->appendChild(self::getNewTextAttribute($xml, 'content', self::CONTENT_TYPE));
        $body->appendChild(self::getNewTextAttribute($xml, 'rid', $this->getAndIncrementRid()));
        $body->appendChild(self::getNewTextAttribute($xml, 'xml:lang', self::XML_LANG));

        if ($this->sid != '') {
            $body->appendChild(self::getNewTextAttribute($xml, 'sid', $this->sid));
        }

        return $xml;
    }

    /**
     * Get node (username) from jid
     *
     * @param string $jid
     * @return string Node
     */
    public static function getNodeFromJid($jid) {
        $atPos = strpos($jid, '@');
        if ($atPos === false) {
            return '';
        }
        return substr($jid, 0, $atPos);
    }

    /**
     * Append new attribute to existing DOMDocument.
     *
     * @param DOMDocument $domDocument
     * @param string $attributeName
     * @param string $value
     * @return DOMNode
     */
    protected static function getNewTextAttribute($domDocument, $attributeName, $value) {
        $attribute = $domDocument->createAttribute($attributeName);
        $attribute->appendChild($domDocument->createTextNode($value));

        return $attribute;
    }

    /**
     * Get body node from DOMDocument
     *
     * @param DOMDocument $domDocument
     * @return DOMNode
     */
    protected static function getBodyFromDomDocument($domDocument) {
        $body = $domDocument->getElementsByTagName('body');
        return $body->item(0);
    }

    /**
     * Parse XML and return DOMNode of the body
     *
     * @uses XmppPrebind::getBodyFromDomDocument()
     * @param string $xml
     * @return DOMNode
     */
    protected static function getBodyFromXml($xml) {
        $domDocument = new DOMDocument();
        $domDocument->loadXml($xml);

        return self::getBodyFromDomDocument($domDocument);
    }

    /**
     * Get the rid and increment it by one.
     * Required by RFC
     *
     * @return int
     */
    protected function getAndIncrementRid() {
        return $this->rid++;
    }

}

/**
 * Standard XmppPrebind Exception
 */
class XmppPrebindException extends Exception {
    
}

class XmppPrebindConnectionException extends XmppPrebindException {
    
}