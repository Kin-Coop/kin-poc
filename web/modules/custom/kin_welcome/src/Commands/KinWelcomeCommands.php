<?php

  namespace Drupal\kin_welcome\Commands;

  use Drush\Commands\DrushCommands;
  use Drupal\user\Entity\User;

  /**
   * Drush commands for kin_welcome module.
   */
  class KinWelcomeCommands extends DrushCommands {

    /**
     * Mark all existing users as having seen the welcome modal.
     *
     * @command kin-welcome:mark-all-seen
     * @aliases kwmas
     * @usage kin-welcome:mark-all-seen
     *   Mark all existing users as having seen the welcome modal.
     */
    public function markAllSeen() {
      $user_data = \Drupal::service('user.data');

      // Get all user IDs except anonymous (uid 0)
      $uids = \Drupal::entityQuery('user')
                     ->condition('uid', 0, '>')
                     ->accessCheck(FALSE)
                     ->execute();

      if (empty($uids)) {
        $this->logger()->warning('No users found.');
        return;
      }

      $count = 0;
      foreach ($uids as $uid) {
        // Check if they already have the flag set
        $has_seen = $user_data->get('kin_welcome', $uid, 'has_seen_welcome_modal');

        if (!$has_seen) {
          // Set the flag
          $user_data->set('kin_welcome', $uid, 'has_seen_welcome_modal', TRUE);

          // Also clear the initial_login flag if it exists
          $user_data->delete('kin_welcome', $uid, 'initial_login');

          $count++;
        }
      }

      $this->logger()->success(dt('Marked @count user(s) as having seen the welcome modal.', ['@count' => $count]));
    }

    /**
     * Reset a specific user's welcome modal flags.
     *
     * @param int $uid
     *   The user ID to reset.
     *
     * @command kin-welcome:reset-user
     * @aliases kwru
     * @usage kin-welcome:reset-user 123
     *   Reset the welcome modal flags for user 123.
     */
    public function resetUser($uid) {
      $user = User::load($uid);

      if (!$user) {
        $this->logger()->error(dt('User @uid not found.', ['@uid' => $uid]));
        return;
      }

      $user_data = \Drupal::service('user.data');

      // Delete both flags
      $user_data->delete('kin_welcome', $uid, 'has_seen_welcome_modal');
      $user_data->delete('kin_welcome', $uid, 'initial_login');

      $this->logger()->success(dt('Reset welcome modal flags for user @uid (@name).', [
        '@uid' => $uid,
        '@name' => $user->getDisplayName(),
      ]));
    }

    /**
     * Show welcome modal statistics.
     *
     * @command kin-welcome:stats
     * @aliases kwstats
     * @usage kin-welcome:stats
     *   Display statistics about welcome modal views.
     */
    public function stats() {
      $user_data = \Drupal::service('user.data');

      // Get all users
      $uids = \Drupal::entityQuery('user')
                     ->condition('uid', 0, '>')
                     ->accessCheck(FALSE)
                     ->execute();

      $total_users = count($uids);
      $seen_count = 0;
      $pending_count = 0;

      foreach ($uids as $uid) {
        $has_seen = $user_data->get('kin_welcome', $uid, 'has_seen_welcome_modal');
        if ($has_seen) {
          $seen_count++;
        } else {
          $pending_count++;
        }
      }

      $this->output()->writeln('Welcome Modal Statistics:');
      $this->output()->writeln('------------------------');
      $this->output()->writeln(dt('Total users: @total', ['@total' => $total_users]));
      $this->output()->writeln(dt('Users who have seen modal: @seen', ['@seen' => $seen_count]));
      $this->output()->writeln(dt('Users who haven\'t seen modal: @pending', ['@pending' => $pending_count]));
    }

  }
