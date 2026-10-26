<?php

declare(strict_types=1);

namespace App\Services\QA;

use App\Models\QaReview;
use App\Models\VideoProject;
use Illuminate\Support\Facades\Log;

class QaReviewer
{
    /**
     * Run QA review on a video project. Checks scenes, assets, script, and metadata.
     */
    public function review(VideoProject $project, ?int $reviewerId = null): QaReview
    {
        $project->load(['channel', 'currentScript', 'scenes', 'assets']);
        $round = ($project->qaReviews()->max('round') ?? 0) + 1;

        $issues = [];
        $checklist = [];
        $totalScore = 0;
        $checkCount = 0;

        // Check 1: Script exists and is approved
        $result = $this->checkScript($project);
        $checklist[] = $result['check'];
        if ($result['issue']) $issues[] = $result['issue'];
        $totalScore += $result['score'];
        $checkCount++;

        // Check 2: All scenes have narration
        $result = $this->checkSceneNarration($project);
        $checklist[] = $result['check'];
        foreach ($result['issues'] as $issue) $issues[] = $issue;
        $totalScore += $result['score'];
        $checkCount++;

        // Check 3: Scene durations add up
        $result = $this->checkDuration($project);
        $checklist[] = $result['check'];
        if ($result['issue']) $issues[] = $result['issue'];
        $totalScore += $result['score'];
        $checkCount++;

        // Check 4: Visual prompts are filled
        $result = $this->checkVisualPrompts($project);
        $checklist[] = $result['check'];
        foreach ($result['issues'] as $issue) $issues[] = $issue;
        $totalScore += $result['score'];
        $checkCount++;

        // Check 5: Assets exist
        $result = $this->checkAssets($project);
        $checklist[] = $result['check'];
        if ($result['issue']) $issues[] = $result['issue'];
        $totalScore += $result['score'];
        $checkCount++;

        // Check 6: On-screen text in hook scene
        $result = $this->checkHookText($project);
        $checklist[] = $result['check'];
        if ($result['issue']) $issues[] = $result['issue'];
        $totalScore += $result['score'];
        $checkCount++;

        $avgScore = $checkCount > 0 ? (int) round($totalScore / $checkCount) : 0;
        $overallResult = empty($issues) ? 'pass' : ($avgScore >= 70 ? 'warn' : 'fail');

        $summary = sprintf(
            'QA Round %d: %d checks, %d issues found. Score: %d/100. %s',
            $round,
            $checkCount,
            count($issues),
            $avgScore,
            $overallResult === 'pass' ? 'All checks passed!' : 'Issues need attention.'
        );

        Log::info('QA review completed', [
            'project' => $project->id,
            'round'   => $round,
            'score'   => $avgScore,
            'issues'  => count($issues),
        ]);

        return QaReview::create([
            'video_project_id' => $project->id,
            'round'            => $round,
            'overall_result'   => $overallResult,
            'score'            => $avgScore,
            'issues'           => $issues,
            'checklist'        => $checklist,
            'summary'          => $summary,
            'reviewed_by'      => $reviewerId,
        ]);
    }

    private function checkScript(VideoProject $project): array
    {
        $script = $project->approvedScript();
        if (! $script) {
            return [
                'check' => ['name' => 'Approved Script', 'status' => 'fail'],
                'issue' => ['type' => 'missing_script', 'severity' => 'critical', 'message' => 'No approved script version found.', 'timestamp' => null, 'resolved' => false],
                'score' => 0,
            ];
        }
        return [
            'check' => ['name' => 'Approved Script', 'status' => 'pass'],
            'issue' => null,
            'score' => 100,
        ];
    }

    private function checkSceneNarration(VideoProject $project): array
    {
        $scenes = $project->scenes;
        $issues = [];
        $score = 100;

        foreach ($scenes as $scene) {
            if (empty($scene->narration_text)) {
                $issues[] = [
                    'type'      => 'missing_narration',
                    'severity'  => 'major',
                    'message'   => "Scene #{$scene->order} has no narration text.",
                    'timestamp' => $this->sceneTimestamp($project, $scene->order),
                    'resolved'  => false,
                ];
                $score -= 20;
            }
        }

        return [
            'check'  => ['name' => 'Scene Narration', 'status' => empty($issues) ? 'pass' : 'fail'],
            'issues' => $issues,
            'score'  => max(0, $score),
        ];
    }

    private function checkDuration(VideoProject $project): array
    {
        $totalDuration = $project->totalSceneDuration();
        $target = $project->target_duration;

        if ($totalDuration === 0.0) {
            return [
                'check' => ['name' => 'Duration Check', 'status' => 'fail'],
                'issue' => ['type' => 'no_duration', 'severity' => 'critical', 'message' => 'No scenes have duration set.', 'timestamp' => null, 'resolved' => false],
                'score' => 0,
            ];
        }

        $diff = abs($totalDuration - $target);
        if ($diff > 10) {
            return [
                'check' => ['name' => 'Duration Check', 'status' => 'warn'],
                'issue' => ['type' => 'duration_mismatch', 'severity' => 'minor', 'message' => "Total scene duration ({$totalDuration}s) differs from target ({$target}s) by {$diff}s.", 'timestamp' => null, 'resolved' => false],
                'score' => 60,
            ];
        }

        return [
            'check' => ['name' => 'Duration Check', 'status' => 'pass'],
            'issue' => null,
            'score' => 100,
        ];
    }

    private function checkVisualPrompts(VideoProject $project): array
    {
        $scenes = $project->scenes;
        $issues = [];
        $score = 100;

        foreach ($scenes as $scene) {
            if (empty($scene->visual_prompt)) {
                $issues[] = [
                    'type'      => 'missing_visual',
                    'severity'  => 'major',
                    'message'   => "Scene #{$scene->order} has no visual prompt.",
                    'timestamp' => $this->sceneTimestamp($project, $scene->order),
                    'resolved'  => false,
                ];
                $score -= 15;
            }
        }

        return [
            'check'  => ['name' => 'Visual Prompts', 'status' => empty($issues) ? 'pass' : 'fail'],
            'issues' => $issues,
            'score'  => max(0, $score),
        ];
    }

    private function checkAssets(VideoProject $project): array
    {
        $assetCount = $project->assets()->count();
        if ($assetCount === 0) {
            return [
                'check' => ['name' => 'Assets Uploaded', 'status' => 'warn'],
                'issue' => ['type' => 'no_assets', 'severity' => 'minor', 'message' => 'No assets uploaded yet.', 'timestamp' => null, 'resolved' => false],
                'score' => 40,
            ];
        }
        return [
            'check' => ['name' => 'Assets Uploaded', 'status' => 'pass'],
            'issue' => null,
            'score' => 100,
        ];
    }

    private function checkHookText(VideoProject $project): array
    {
        $firstScene = $project->scenes()->orderBy('order')->first();
        if ($firstScene && empty($firstScene->on_screen_text)) {
            return [
                'check' => ['name' => 'Hook On-Screen Text', 'status' => 'warn'],
                'issue' => ['type' => 'missing_hook_text', 'severity' => 'minor', 'message' => 'First scene has no on-screen text. Consider adding a text hook.', 'timestamp' => '0:00', 'resolved' => false],
                'score' => 60,
            ];
        }
        return [
            'check' => ['name' => 'Hook On-Screen Text', 'status' => 'pass'],
            'issue' => null,
            'score' => 100,
        ];
    }

    private function sceneTimestamp(VideoProject $project, int $sceneOrder): string
    {
        $elapsed = 0;
        foreach ($project->scenes as $scene) {
            if ($scene->order >= $sceneOrder) break;
            $elapsed += (float) $scene->duration_seconds;
        }
        $secs = (int) $elapsed;
        return sprintf('%d:%02d', intdiv($secs, 60), $secs % 60);
    }
}