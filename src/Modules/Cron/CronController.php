<?php

namespace App\Modules\Cron;

use App\Auth\LocalAuth;
use App\Core\Redirect;
use App\Core\Session;
use App\Core\View;
use App\Queue\QueueDispatcher;
use App\Queue\QueueWorker;

class CronController
{
    public function index(): void
    {
        LocalAuth::requireAdmin();

        $runner    = new CronRunner(app_graph());
        $jobs      = $runner->getJobStates();
        $queueStats = QueueDispatcher::stats();
        $queueItems = QueueWorker::recentItems(30);

        View::render('cron/index', [
            'pageTitle'   => t('Cron & Automatisierung'),
            'jobs'        => $jobs,
            'queueStats'  => $queueStats,
            'queueItems'  => $queueItems,
            'flash'       => Session::getFlash('success'),
            'error'       => Session::getFlash('error'),
        ]);
    }

    /** POST /cron/update-job — save interval + enabled for one job. */
    public function updateJob(string $jobKey): void
    {
        LocalAuth::requireAdmin();

        $enabled  = isset($_POST['enabled']);
        $interval = max(1, (int)($_POST['interval_minutes'] ?? 60));

        (new CronRunner(app_graph()))->updateJob($jobKey, $enabled, $interval);

        Session::flash('success', t('Job ":jobKey" aktualisiert.', ['jobKey' => $jobKey]));
        Redirect::to('/cron');
    }

    /** POST /cron/run-job/{key} — manually trigger a job (admin only). */
    public function runJob(string $jobKey): void
    {
        LocalAuth::requireAdmin();

        $result = (new CronRunner(app_graph()))->runJob($jobKey);

        if ($result['status'] === 'success') {
            Session::flash('success', t('Job ausgeführt (:secondss): :log', ['seconds' => $result['seconds'], 'log' => $result['log']]));
        } else {
            Session::flash('error', t('Job fehlgeschlagen: :log', ['log' => $result['log']]));
        }
        Redirect::to('/cron');
    }

    /** POST /cron/queue/retry — retry all failed queue items. */
    public function retryFailed(): void
    {
        LocalAuth::requireAdmin();
        QueueDispatcher::retryFailed();
        Session::flash('success', t('Fehlgeschlagene Jobs zurückgesetzt.'));
        Redirect::to('/cron');
    }

    /** POST /cron/queue/prune — delete old completed items. */
    public function pruneQueue(): void
    {
        LocalAuth::requireAdmin();
        QueueDispatcher::pruneCompleted(0); // delete all done items
        Session::flash('success', t('Abgeschlossene Jobs aus der Warteschlange entfernt.'));
        Redirect::to('/cron');
    }
}
