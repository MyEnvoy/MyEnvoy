<?php

namespace Api;

class ApipathWall extends Apipath {

    private $_action = NULL;
    private $_postId = NULL;

    public function pathToAttr($pathParts) {
        $this->_action = isset($pathParts[0]) ? $pathParts[0] : NULL;
        $this->_postId = isset($pathParts[1]) ? (int) $pathParts[1] : NULL;
    }

    protected function executeCall() {
        if (empty($this->_action)) {
            return $this->getWholeWall();
        }

        $action = $this->_action . 'Action';
        if (method_exists($this, $action)) {
            return $this->$action();
        }

        throw new \Exception('API Endpoint not found.', \Errorcode::API_ENDPOINT_NOT_FOUND);
    }

    private function getWholeWall() {
        $this->throwExceptionOnTooLongPath(1);
        $user = \Currentuser::getEnsuredLoggedInUserByJwt($this->getBearer());

        $res = new \stdClass();

        $res->wall = array();

        foreach ($user->getWall() as $entry) {
            $entry['post']->addJsonData('hasFavorised', $user->hasFavourised($entry['post']->getId()));
            foreach ($entry['comments'] as &$comment) {
                $comment['comment']->addJsonData('hasFavorised', $user->hasFavourised($comment['comment']->getId()));

                foreach ($comment['subcomments'] as &$subcomment) {
                    $subcomment->addJsonData('hasFavorised', $user->hasFavourised($subcomment->getId()));
                }
            }
            $res->wall[] = $entry;
        }

        return $res;
    }

    private function favAction() {
        $this->throwExceptionOnTooLongPath(2);
        $this->hasToBePost();
        $user = \Currentuser::getEnsuredLoggedInUserByJwt($this->getBearer());

        $res = new \stdClass();

        $post = \Post::getById($this->_postId);

        if (empty($this->_postId) || $post === NULL) {
            throw new \Exception('API Endpoint not found.', \Errorcode::API_ENDPOINT_NOT_FOUND);
        }

        $post->fav($user);
        
        $res->success = TRUE;

        return $res;
    }
    
    private function defavAction() {
        $this->throwExceptionOnTooLongPath(2);
        $this->hasToBePost();
        $user = \Currentuser::getEnsuredLoggedInUserByJwt($this->getBearer());

        $res = new \stdClass();

        $post = \Post::getById($this->_postId);

        if (empty($this->_postId) || $post === NULL) {
            throw new \Exception('API Endpoint not found.', \Errorcode::API_ENDPOINT_NOT_FOUND);
        }

        $post->defav($user);
        
        $res->success = TRUE;

        return $res;
    }

}
