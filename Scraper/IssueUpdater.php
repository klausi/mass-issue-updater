<?php

namespace Drupal\MassIssueUpdater\Scraper;

use Goutte\Client;

class IssueUpdater {
  
  private $issueUris;

  private $status;

  private $comment;

  /**
   * Constructs a new IssueUpdater object.
   *
   * @param string[] $issue_uris
   *   The list of issue URIs that should be updated.
   */
  public function __construct(array $issue_uris) {
    $this->issueUris = $issue_uris;
  }

  /**
   * Set the status of issues that should be updated to.
   *
   * @param int $status
   *   The issue status constant from IssueFinder.
   *
   * @return $this
   */
  public function setStatus($status) {
    $this->status = $status;
    return $this;
  }

  /**
   * Set the text comment that should be added to the issue thread.
   *
   * @param int $comment
   *   The comment text.
   *
   * @return $this
   */
  public function setComment($comment) {
    $this->comment = $comment;
    return $this;
  }

  public function setUser($user) {
    $this->user = $user;
    return $this;
  }

  public function setPassword($password) {
    $this->password = $password;
    return $this;
  }

  /**
   * Executes the issue update on all issues that have been configured.
   */
  public function execute() {
    if (empty($this->issueUris)) {
      return;
    }
    $form_values = [];
    if ($this->comment) {
      $form_values['nodechanges_comment[comment_body][und][0][value]'] = $this->comment;
    }
    if ($this->status) {
      $form_values['field_issue_status[und]'] = $this->status;
    }
    if (empty($form_values)) {
      throw new Exception('No issues changes set, so there is nothing to update on the issues.');
    }

    $client = $this->login();

    foreach ($this->issueUris as $issue_uri) {
      $issue_page = $client->request('GET', $issue_uri);
      $comment_form = $issue_page->selectButton('Save')->form();

      // We need to HTML entity decode the issue summary here, otherwise we
      // would post back a double-encoded version, which would result in issue
      // summary changes that we don't want to touch.
      $form_values['body[und][0][value]'] = html_entity_decode($comment_form->get('body[und][0][value]')->getValue(), ENT_QUOTES, 'UTF-8');

      do {
        // Repeat the form submission if there is a 502 gateway error.
        $client->submit($comment_form, $form_values);
        $response = $client->getResponse();
      } while ($response->getStatus() != 200);
    }
  }

  /**
   * Performs the user login on drupal.org.
   *
   * @return \Goutte\Client
   *   The client holding the authenticated session.
   */
  private function login() {
    $client = new Client();
    $crawler = $client->request('GET', 'https://www.drupal.org/user');
    $form = $crawler->selectButton('Log in')->form();
    $crawler = $client->submit($form, [
      'name' => $this->user,
      'pass' => $this->password,
    ]);
    // @todo this does not work for 2 factor authentication accounts.

    $login_errors = $crawler->filter('.messages-error');
    if ($login_errors->count() > 0) {
      throw new Exception('Login on drupal.org failed for user ' . $this->user);
    }
    return $client;
  }

}