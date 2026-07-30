<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case RequirementGathering = 'requirement_gathering';
    case UiUxDesign = 'ui_ux_design';
    case Development = 'development';
    case QaTesting = 'qa_testing';
    case ClientReview = 'client_review';
    case Revision = 'revision';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function isActive(): bool
    {
        return ! in_array($this, [self::Pending, self::Completed, self::Cancelled], true);
    }
}
