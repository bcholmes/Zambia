insert into `Phases` (phasename, current, notes, implemented, display_order)
values ('Feedback and Interest', 0, 'Add on Session Feedback', 1, 1000);

insert into `PermissionAtoms` (permatomtag, page, notes) 
values ('SessionFeedback', 'SessionFeedback', 'user can provide session feedback');

insert into `Permissions` (permatomid, phaseid, permroleid)
select a.permatomid, p.phaseid, r.permroleid
from PermissionAtoms a,
     Phases p,
     PermissionRoles r
where a.permatomtag = 'SessionFeedback'
  and p.phasename = 'Feedback and Interest'
  and r.permrolename = 'Program Participant';