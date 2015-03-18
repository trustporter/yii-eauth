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
    return (array) $this->makeSignedRequest('https://graph.facebook.com/me?fields=' . $fields);
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
   * Closes the popup window and calls the appropriate js function of the opener window.
   *
   * @var boolean $success if the authentication succeeded or not.
   */
  public function ajaxRedirect($success)
  {
    $this->component->ajaxRedirect($success, SocialNetwork::FACEBOOK);
  }
}
?>
