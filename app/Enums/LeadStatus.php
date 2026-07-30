<?php

namespace App\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Viewed = 'viewed';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case ProposalSent = 'proposal_sent';
    case Won = 'won';
    case Lost = 'lost';
    case Closed = 'closed';
    case Read = 'read';
    case Replied = 'replied';
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Quoted = 'quoted';
    case Negotiation = 'negotiation';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Converted = 'converted';
}
