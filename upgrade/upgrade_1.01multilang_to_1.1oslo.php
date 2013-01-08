<?php
// Various fixes implemented in the Oslo fork of Local Exchange
$running_upgrade_script = true;
include_once("../includes/inc.global.php");

// Indexes on the trades table
$cDB->Query("CREATE INDEX trades_from ON ". DATABASE_TRADES ." (member_id_from)");
$cDB->Query("CREATE INDEX trades_to ON ". DATABASE_TRADES ." (member_id_to)");

// Missing option for DAILY updates (supported by the newsletter code)
$cDB->Query("UPDATE `settings` SET options='NEVER,DAILY,WEEKLY,MONTHLY'
	WHERE name='DEFAULT_UPDATE_INTERVAL'");

// Karma points

// TODO This implementation of karma, which seemed like a good idea at the time,
// is rather complex (it involves a Cartesian join over members and trades) and incohesive
// (member_directory joins with a view, whereas LoadMember sums using a different query).
// Consider rewriting it to work more like balances do. See comments at
// https://trello.com/card/game-mechanics/4f2af6dc418686231b0f75e4/21

// First, a cartesian view of all members paired with all trades.
$cDB->Query(<<<SQL
CREATE view member_x_trades AS
SELECT m.member_id, t.trade_id
		FROM member m, trades t
SQL
);

// Then, views for all spent and earned credits.
$cDB->Query(<<<SQL
CREATE VIEW trades_from AS
SELECT member_id, trade_id, amount
FROM member m, trades t
WHERE member_id = member_id_from
	AND type NOT IN ('M', 'N')
	AND t.status NOT IN ('M', 'N')
SQL
);

$cDB->Query(<<<SQL
CREATE VIEW trades_to AS
SELECT member_id, trade_id, amount
FROM member m, trades t
WHERE member_id = member_id_to
	AND type NOT IN ('M', 'N')
	AND t.status NOT IN ('M', 'N')
SQL
);

// Then, a calculation of karma.

$cDB->Query(<<<SQL
CREATE VIEW karma AS
SELECT mt.member_id, SUM(t_from.amount) AS spent, SUM(t_to.amount) AS earned, LEAST(SUM(t_from.amount), SUM(t_to.amount)) AS karma
FROM member_x_trades AS mt
	LEFT JOIN trades_from t_from
	ON mt.trade_id = t_from.trade_id AND mt.member_id = t_from.member_id
	LEFT JOIN trades_to t_to
	ON mt.trade_id = t_to.trade_id AND mt.member_id = t_to.member_id
GROUP BY member_id
ORDER BY karma DESC
SQL
);

echo (_("Database has been updated to version ")."1.1oslo.");
?>
