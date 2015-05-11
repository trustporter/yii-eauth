<?php
class CustomGoogleOAuthService extends GoogleOAuthService
{
  protected $scope = 'https://www.googleapis.com/auth/userinfo.email&https://www.googleapis.com/auth/userinfo.profile';

  public function getInterestingInfo($fields = "")
  {
    $info = (array) $this->makeSignedRequest('https://www.googleapis.com/oauth2/v3/userinfo');
    return $info;
  }
}
?>
