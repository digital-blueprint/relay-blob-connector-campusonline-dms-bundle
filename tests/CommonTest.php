<?php

declare(strict_types=1);

use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Entity\Error;
use Dbp\Relay\BlobConnectorCampusonlineDmsBundle\Rest\Common;
use PHPUnit\Framework\TestCase;

class CommonTest extends TestCase
{
    public function testValidLowercaseUuid(): void
    {
        $this->expectNotToPerformAssertions();
        Common::validateUuid('userId', '550e8400-e29b-41d4-a716-446655440000');
    }

    public function testValidUppercaseUuid(): void
    {
        $this->expectNotToPerformAssertions();
        Common::validateUuid('userId', '550E8400-E29B-41D4-A716-446655440000');
    }

    public function testValidMixedCaseUuid(): void
    {
        $this->expectNotToPerformAssertions();
        Common::validateUuid('userId', '550e8400-E29B-41d4-A716-446655440000');
    }

    public function testThrowsOnEmptyString(): void
    {
        $this->expectException(Error::class);
        Common::validateUuid('userId', '');
    }

    public function testThrowsOnMissingHyphens(): void
    {
        $this->expectException(Error::class);
        Common::validateUuid('userId', '550e8400e29b41d4a716446655440000');
    }
}
