<?php

/**
 * @author Tural Aliyev <aliyev@tural.us>
 * @license MIT
 * @copyright (c) 2015, Tural Aliyev
 * 
 * With adaptions by MyEnvoy
 */

namespace Thirdparty;

class XmppPrebind {
    
    private $_BOSH_URL;
    protected $_sid;
    protected $_rid;
    protected $_curl;

    /**
     * @var \Currentuser
     */
    protected $_user;

    public function __construct(\Currentuser $user) {
        $this->_BOSH_URL = \Server::getRootLink() . 'http-bind';
        $this->_user = $user;
    }

    public function getRid() {
        return $this->_rid;
    }

    private function getNextRid() {
        return ++$this->_rid;
    }

    public function getSid() {
        return $this->_sid;
    }

    public function getJid() {
        return $this->_user->getName() . '@' . \Server::getMyHost();
    }

    public function getAuth() {
        $hash = base64_encode($this->getJid() .
                        '\0' . $this->_user->getName() .
                        '\0' . $this->_user->getXmppPwd());
        $this->_rid = rand();

        $return = $this->sendBody(sprintf('<body rid="%s"
                    xmlns="http://jabber.org/protocol/httpbind"
                    to="%s" xml:lang="en" wait="0" hold="1"
                    content="text/xml; charset=utf-8"
                    ver="1.6" xmpp:version="1.0" xmlns:xmpp="urn:xmpp:xbosh"/>', $this->getRid(), \Server::getMyHost()), TRUE);

        $xml = new \SimpleXMLElement($return);        
        var_dump_pre($xml);

        $this->_sid = $xml['sid'];
        echo $this->sendBody(sprintf('<body rid="%s" xmlns="http://jabber.org/protocol/httpbind" sid="$sid">
                    <auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="PLAIN">%s</auth>
                </body>', $this->getNextRid(), $this->getSid(), $hash));

        echo $this->sendBody(sprintf('<body rid="%s" 
                xmlns="http://jabber.org/protocol/httpbind" 
                sid="%s" to="%s" xml:lang="en" xmpp:restart="true"
                xmlns:xmpp="urn:xmpp:xbosh"/>', $this->getNextRid(), $this->getSid(), \Server::getMyHost()));

        echo $this->sendBody(sprintf('<body rid="%s" xmlns="http://jabber.org/protocol/httpbind" sid="%s">
                        <iq type="set" id="_bind_auth_2" xmlns="jabber:client">
                            <bind xmlns="urn:ietf:params:xml:ns:xmpp-bind"/>
                        </iq>
                </body>', $this->getNextRid(), $this->getSid()));

        echo $this->sendBody(sprintf('<body rid="%s" xmlns="http://jabber.org/protocol/httpbind" sid="%s">
            <iq type="set" id="_session_auth_2" xmlns="jabber:client">
                <session xmlns="urn:ietf:params:xml:ns:xmpp-session"/>
            </iq>
        </body>', $this->getNextRid(), $this->getSid()));

        echo $this->sendBody(sprintf('<body rid="%s" sid="%s" xmlns="http://jabber.org/protocol/httpbind">
                  <iq id="bind_1" type="set" xmlns="jabber:client">
                    <bind xmlns="urn:ietf:params:xml:ns:xmpp-bind">
                      <resource>httpclient</resource>
                    </bind>
                  </iq>
                </body>', $this->getNextRid(), $this->getSid()));

        $this->getNextRid();

        $this->_sid = (array) $this->getSid();
        $this->_sid = $this->getSid()[0];

        curl_close($this->getCurl());

        return [
            'sid' => $this->getSid(),
            'rid' => $this->getRid(),
            'jid' => $this->_user->getFullQualifiedName()
        ];
    }

    private function getCurl() {
        if (empty($this->_curl)) {
            $this->_curl = curl_init($this->_BOSH_URL);
            curl_setopt($this->_curl, CURLOPT_HEADER, FALSE);
            curl_setopt($this->_curl, CURLOPT_POST, TRUE);
            curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($this->_curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: text/xml; charset=utf-8'
            ));
            curl_setopt($this->_curl, CURLOPT_VERBOSE, FALSE);
        }

        return $this->_curl;
    }

    private function sendBody($body, $return_transfer = TRUE) {
        $ch = $this->getCurl();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $return_transfer);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        return curl_exec($ch);
    }

}
