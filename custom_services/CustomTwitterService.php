<?php
/**
 * An example of extending the provider class.
 *
 * @author Maxim Zemskov <nodge@yandex.ru>
 * @link http://github.com/Nodge/yii-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(dirname(__FILE__)) . '/services/TwitterOAuthService.php';

class CustomTwitterService extends TwitterOAuthService {

	protected function fetchAttributes() {
		$info = $this->makeSignedRequest('https://api.twitter.com/1.1/account/verify_credentials.json');

		$this->attributes['id'] = $info->id;
		$this->attributes['name'] = $info->name;
		$this->attributes['url'] = 'http://twitter.com/account/redirect_by_id?id=' . $info->id_str;

		$this->attributes['username'] = $info->screen_name;
		$this->attributes['language'] = $info->lang;
		$this->attributes['timezone'] = timezone_name_from_abbr('', $info->utc_offset, date('I'));
		$this->attributes['photo'] = $info->profile_image_url;
	}

  public function getInterestingInfo($fields = "")
  {
    $info = (array) $this->makeSignedRequest('https://api.twitter.com/1.1/account/verify_credentials.json?include_email=true');
    Yii::log(CVarDumper::dumpAsString($info));
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
      $info = (array) $this->makeSignedRequest("https://api.twitter.com/1.1/account/verify_credentials.json");
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
  public function getAllMyFriends($userId)
  {
    $friends = array();
    $cursor = -1;
    $limit = 200;
    while ($cursor != 0)
    {
      $data = (array) $this->makeSignedRequest("https://api.twitter.com/1.1/friends/list.json?cursor=$cursor&user_id=$userId");
      //Yii::log(CVarDumper::dumpAsString($data));
      $cursor = (int) $data["next_cursor"];
      $friends = array_merge($friends, $data["users"]);
    }
    return array_map(function($of) {
        $f = (array) $of;
        return $f;
      }, $friends);
  }
}
