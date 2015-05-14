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
      $friend['email'] = $email->address;
      $friend['alias'] = $entry->title->{'$t'};
      if (property_exists($entry, 'gd$phoneNumber'))
      {
        $phone = reset($entry->{'gd$phoneNumber'});
        $friend['phone_number'] = $phone->{'$t'};
      }
      $friends[] = $friend;
    }
    return $friends;
  }
}
?>
