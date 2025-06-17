<?php

namespace Drupal\kin_civi\Service;

use Symfony\Component\Routing\Route;

class KinAccessChecker {

  public function access(Route $route) {
    //return $this->isUserTheGatekeeper($account) || $this->isUserTheKeymaster($account);
    return FALSE;
  }

}
