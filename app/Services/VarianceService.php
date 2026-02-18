<?php

namespace App\Services;

use App\Models\OpnameEntry;
use App\Models\OpnameSession;
use App\Models\VarianceReview;
use App\Models\ActivityLog;

class VarianceService
{
    /**
     * Variance thresholds for auto-routing
     */
    const THRESHOLD_AUTO_APPROVE = 2;
    const THRESHOLD_SUPERVISOR = 10;

    /**
     * Process all entries in a session — calculate variance and create reviews
     */
    public function processSession(OpnameSession $session): array
    {
        $stats = ['total' => 0, 'auto_approved' => 0, 'pending' => 0, 'escalated' => 0];

        $entries = $session->entries()->with('item')->get();

        foreach ($entries as $entry) {
            $entry->calculateVariance();
            $entry->save();

            $review = $this->createOrUpdateReview($entry);
            $stats['total']++;
            $stats[$this->mapStatusToStat($review->status)]++;
        }

        // Update session status
        $session->update(['status' => 'completed', 'completed_at' => now()]);

        ActivityLog::log($session, 'processed', null, $stats);

        return $stats;
    }

    /**
     * Classify severity based on absolute variance
     */
    public function classifySeverity(float $absVariance): string
    {
        if ($absVariance <= self::THRESHOLD_AUTO_APPROVE) {
            return 'low';
        } elseif ($absVariance <= 5) {
            return 'medium';
        } elseif ($absVariance <= self::THRESHOLD_SUPERVISOR) {
            return 'high';
        }
        return 'critical';
    }

    /**
     * Determine routing status based on variance
     */
    public function determineStatus(float $absVariance): string
    {
        if ($absVariance <= self::THRESHOLD_AUTO_APPROVE) {
            return 'auto_approved';
        } elseif ($absVariance <= self::THRESHOLD_SUPERVISOR) {
            return 'pending';
        }
        return 'escalated';
    }

    /**
     * Create or update a variance review for an entry
     */
    public function createOrUpdateReview(OpnameEntry $entry): VarianceReview
    {
        $absVariance = abs((float) $entry->variance);
        $severity = $this->classifySeverity($absVariance);
        $status = $this->determineStatus($absVariance);

        $review = VarianceReview::updateOrCreate(
            ['opname_entry_id' => $entry->id],
            [
                'severity' => $severity,
                'status' => $status,
                'auto_resolved' => $status === 'auto_approved',
                'reviewed_at' => $status === 'auto_approved' ? now() : null,
            ]
        );

        $action = $status === 'auto_approved' ? 'auto_approved' : ($status === 'escalated' ? 'escalated' : 'pending_review');
        ActivityLog::log($review, $action, null, [
            'variance' => $entry->variance,
            'severity' => $severity,
        ]);

        return $review;
    }

    /**
     * Approve a variance review
     */
    public function approve(VarianceReview $review, int $userId, ?string $notes = null): VarianceReview
    {
        $review->update([
            'status' => 'approved',
            'reviewed_by' => $userId,
            'reviewed_at' => now(),
            'resolution_notes' => $notes,
        ]);

        ActivityLog::log($review, 'approved', $userId, ['notes' => $notes]);

        return $review;
    }

    /**
     * Reject a variance review
     */
    public function reject(VarianceReview $review, int $userId, ?string $notes = null): VarianceReview
    {
        $review->update([
            'status' => 'rejected',
            'reviewed_by' => $userId,
            'reviewed_at' => now(),
            'resolution_notes' => $notes,
        ]);

        ActivityLog::log($review, 'rejected', $userId, ['notes' => $notes]);

        return $review;
    }

    private function mapStatusToStat(string $status): string
    {
        return match ($status) {
            'auto_approved' => 'auto_approved',
            'escalated' => 'escalated',
            default => 'pending',
        };
    }
}
