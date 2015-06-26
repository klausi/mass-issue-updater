<?php

namespace Drupal\MassIssueUpdater\Scraper;

use Goutte\Client;

class IssueFinder {
  
  const STATUS_POSTPONED_NEEDS_MORE_INFO = 16;
  const STATUS_CLOSED_WORKS_AS_DESIGNED = 6;

  private $project;
  private $status;
  private $updatedBefore;

  /**
   * Set the project issue queue where issues should be searched.
   *
   * @param string $project
   *   The project name on drupal.org, example: "coder".
   *
   * @return $this
   */
  public function setProject($project) {
    $this->project = $project;
    return $this;
  }

  /**
   * Set the status of issues that should be found.
   *
   * @param int $status
   *   The issue status constant from this class.
   *
   * @return $this
   */
  public function setStatus($status) {
    $this->status = $status;
    return $this;
  }

  /**
   * Set the last updated date where issues should be found before that.
   *
   * @param int $unix_timestamp
   *   The Unix date timestamp.
   *
   * @return $this
   */
  public function setLastUpdatedBefore($unix_timestamp) {
    $this->updatedBefore = $unix_timestamp;
    return $this;
  }

  /**
   * Find issues that match the set criteria (50 issues max, first page).
   *
   * @return string[]
   *   The issue URIs.
   */
  public function findIssues() {
    $uri = 'https://www.drupal.org/project/issues/search/';
    $uri .= $this->project;
    if ($this->status) {
      $uri .= '?status[0]=' . $this->status;
    }
    $client = new Client();
    $crawler = $client->request('GET', $uri);
    $links = $crawler->filterXPath('//tbody/tr/td[1]/a')->links();
    $intervals = $crawler->filterXPath('//tbody/tr/td[8]');

    $issue_uris = [];
    foreach ($links as $row => $link) {
      if ($this->updatedBefore) {
        $updated_date = strtotime(trim($intervals->getNode($row)->nodeValue) . ' ago');
        // Skip issues that have been updated after the threshold.
        if ($updated_date >= $this->updatedBefore) {
          continue;
        }
      }

      $issue_uris[] = $link->getUri();
    }
    return $issue_uris;
  }

}
