<?php
namespace App\Services;
final class ScraperImportOutcome { public function __construct(public readonly int $accepted, public readonly int $updated, public readonly int $rejected, public readonly bool $complete, public readonly bool $ownershipLost=false) {} }
