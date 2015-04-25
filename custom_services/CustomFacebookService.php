<?php
class CustomFacebookService extends FacebookOAuthService {
  /**
   * https://developers.facebook.com/docs/authentication/permissions/
   */
  protected $scope = 'email,user_location,user_birthday';

  /**
   * http://developers.facebook.com/docs/reference/api/user/
   *
   * @see FacebookOAuthService::fetchAttributes()
   */
  protected function fetchAttributes() {
    $this->attributes = (array) $this->makeSignedRequest('https://graph.facebook.com/me');
  }

  public function getInterestingInfo($fields)
  {
    $info = (array) $this->makeSignedRequest('https://graph.facebook.com/me?fields=' . $fields);
    $Id = $info['id'];
    $info['avatar'] = $this->getAvatarUrlFromId($Id);
    return $info;
  }

  /**
   * Constructs the URL of a user's avatar from their ID.
   *
   * @param string $Id the ID of the user.
   * @return string the URL of the user's avatar.
   */
  protected function getAvatarUrlFromId($Id)
  {
    return "https://graph.facebook.com/{$Id}/picture?width=200&height=200";
  }

  /**
   * Override method and do not store the token in the session
   */
  protected function saveAccessToken($token)
  {
    $this->access_token = $token['access_token'];
  }

  /**
   * Checks if we have a valid connection.
   * @return bool the result.
   */
  public function isUserConnected()
  {
    try
    {
      $this->makeSignedRequest("https://graph.facebook.com/me?fields=id");
      return true;
    }
    catch (EAuthException $e)
    {
      return false;
    }
  }

  /**
   * Returns the social ID of the user, if connected.
   *
   * @return array the result.
   */
  public function getUserId()
  {
    try
    {
      $info = (array) $this->makeSignedRequest("https://graph.facebook.com/me?fields=id");
      return array('connected' => true, 'id' => $info['id']);
    }
    catch (EAuthException $e)
    {
      return array('connected' => false);
    }
  }

  /**
   * Gets a list of friends and for each gets the requested fields.
   *
   * @param string $fields what you want to fetch.
   * @return array the list of friends.
   */
  public function getAllMyFriends($fields = "")
  {
    if ($fields === "")
      $fields = 'first_name,last_name,id,email,birthday,gender,location,username';
    $friends = array();
    $offset = 0;
    $limit = 1000;
    for (;;)
    {
      $data = (array) $this->makeSignedRequest("https://graph.facebook.com/me/friends?limit=$limit&offset=$offset&fields=$fields");
      $friends = array_merge($friends, $data["data"]);
      if (array_key_exists("paging", $data) && isset($data["paging"]->next))
      {
        if (preg_match('/&offset=(\d+)&/', $data["paging"]->next, $matches))
          $offset = (int) ($matches[1]);
        else
        {
          // use this as a last resort, it should not happen
          $offset += $limit;
        }
      }
      else
        break;
    }
    return array_map(function($of) {
        $f = (array) $of;
        $f['avatar'] = $this->getAvatarUrlFromId($f['id']);
        return $f;
      }, $friends);
  }

  /**
   * Closes the popup window and calls the appropriate js function of the opener window.
   *
   * @var boolean $success if the authentication succeeded or not.
   */
  public function ajaxRedirect($success, $referral)
  {
    if ($referral == 'circle')
    {
      $successJS = 'importConnectionsFromSocialNetwork';
      $failureJS = 'failedToConnectToNetworkForImport';
    }
    elseif ($referral == 'request')
    {
      $successJS = 'delayedSendFbAppRequests';
      $failureJS = 'failureToConnectToNetwork';
    }
    $this->component->ajaxRedirect($success, SocialNetwork::FACEBOOK, $successJS, $failureJS);
  }
}
?>
