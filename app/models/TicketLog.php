<?php
 
class TicketLog extends \Eloquent
{
    protected $guarded = array("TicketLogID");

    protected $table = 'tblTicketLog';

    protected $primaryKey = "TicketLogID";


    static  $defaultTicketLogFields = [
        'Type'=>Ticketfields::default_ticket_type,
        'Status'=>Ticketfields::default_status,
        'Priority'=>Ticketfields::default_priority,
        'Group'=>Ticketfields::default_group,
        'Agent'=>Ticketfields::default_agent
    ];

    const TICKET_ACTION_CREATED = 1;
    const TICKET_ACTION_ASSIGNED_TO = 2;
    const TICKET_ACTION_AGENT_REPLIED = 3;
    const TICKET_ACTION_CUSTOMER_REPLIED = 4;
    const TICKET_ACTION_STATUS_CHANGED = 5;
    const TICKET_ACTION_NOTE_ADDED = 6;
    const TICKET_ACTION_FIELD_CHANGED = 7;

    const TICKET_USER_TYPE_ACCOUNT = 1;
    const TICKET_USER_TYPE_CONTACT = 2;
    const TICKET_USER_TYPE_USER = 3;
    const TICKET_USER_TYPE_SYSTEM = 4;

    static  $TicketUserTypes = [ self::TICKET_USER_TYPE_ACCOUNT  => "Account",
        self::TICKET_USER_TYPE_CONTACT  => "Contact",
        self::TICKET_USER_TYPE_USER     => "User",
        self::TICKET_USER_TYPE_SYSTEM   => "System"
    ];


}