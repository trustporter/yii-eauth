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
}
?>
