<?php
class CustomGoogleOAuthService extends GoogleOAuthService
{
  protected $scope = "https://www.googleapis.com/auth/contacts.readonly https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile";

  public function getInterestingInfo($fields = "")
  {
    $info = (array) $this->makeSignedRequest('https://www.googleapis.com/oauth2/v3/userinfo');
    return $info;
  }

  /**
   * Override method and do not store the token in the session
   */
  protected function saveAccessToken($token)
  {
    $this->access_token = $token->access_token;
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
      $info = (array) $this->makeSignedRequest("https://www.googleapis.com/oauth2/v3/userinfo");
      return array('connected' => true, 'id' => $info['sub']);
    }
    catch (EAuthException $e)
    {
      return array('connected' => false);
    }
  }

  /**
   * Gets a list of friends and for each gets the requested fields.
   *
   * @return array the list of friends.
   */
  public function getAllMyFriends()
  {
    $info = (array) $this->makeSignedRequest("https://www.google.com/m8/feeds/contacts/default/full?max-results=9999");
    $feed = $info['feed'];
    $entries = $feed->entry;
    $friends = array();
    foreach ($entries as $entry)
    {
      $friend = array();
      if (!property_exists($entry, 'gd$email'))
        continue;
      $email = reset($entry->{'gd$email'});
      $friend['email'] = strtolower($email->address);
      $friend['alias'] = $entry->title->{'$t'};
      if (property_exists($entry, 'gd$phoneNumber'))
      {
        $phone = reset($entry->{'gd$phoneNumber'});
        $friend['phone_number'] = $phone->{'$t'};
      }

      $friend['photo'] = null;
      if (property_exists($entry, 'link')) {
        foreach ($entry->link as $link) {
          if ($link->rel == "http://schemas.google.com/contacts/2008/rel#photo") {
            $friend['photo'] = $link->href;
          }
        }
      }
      $friends[] = $friend;
    }

    // Log collected Info
    $log = 
      "Google Import" . PHP_EOL .
      "Collected: " . CVarDumper::dumpAsString($info) . PHP_EOL .
      "Parsed: " . CVarDumper::dumpAsString($friends) . PHP_EOL;
    Yii::log($log);

    return $friends;
  }

  public function getPhoto($url)
  {
    $ch = curl_init ($url . "?access_token=" . urlencode($this->access_token));
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    $raw = curl_exec($ch);
    //$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $contentType = "image/jpeg";
    curl_close ($ch);
    return array('contentType' => $contentType, 'data' => $raw);
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
    $this->component->ajaxRedirect($success, SocialNetwork::GOOGLE, $successJS, $failureJS);
  }
}
?>
