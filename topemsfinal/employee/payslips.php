<?php
require __DIR__.'/../includes/bootstrap.php';
require __DIR__.'/../includes/layout.php';
$user=require_role('employee');
$detail=null;
if (isset($_GET['view'])) {
    $q=db()->prepare("SELECT *,DATE_FORMAT(salary_month,'%M %Y') AS month FROM salaries WHERE id=? AND employee_id=?");
    $q->execute([record_id($_GET['view']),$user['id']]);
    $detail=$q->fetch();
    if (!$detail) missing();
}
$q=db()->prepare("SELECT *,DATE_FORMAT(salary_month,'%M %Y') AS month FROM salaries WHERE employee_id=? ORDER BY salary_month DESC");
$q->execute([$user['id']]);
$rows=$q->fetchAll();
page_start('My Salary & Payslips',$user);
?>
<?php if ($detail): ?>
<section class="card payslip" id="payslipDetail" aria-labelledby="payslipTitle">
    <div class="payslip-header">
        <div><p class="payslip-eyebrow">Employee Management System</p><h2 id="payslipTitle">Salary Payslip</h2><p class="payslip-period"><?=h($detail['month'])?></p></div>
        <div class="payslip-reference"><span class="payslip-status <?= $detail['payment_status']==='Paid'?'is-paid':'is-pending' ?>"><?=h($detail['payment_status'])?></span><p>Reference #<?=h($detail['id'])?></p></div>
    </div>
    <dl class="payslip-employee">
        <?php foreach (['Employee name'=>$user['full_name'],'Employee ID'=>$user['employee_code'],'Department'=>$user['department'],'Designation'=>$user['designation']] as $label=>$value): ?>
        <div><dt><?=h($label)?></dt><dd><?=h($value)?></dd></div>
        <?php endforeach; ?>
    </dl>
    <div class="payslip-breakdown">
        <section class="payslip-panel"><h3>Earnings</h3><dl>
            <div><dt>Basic salary</dt><dd><?=money($detail['basic'])?></dd></div>
            <div><dt>Allowances</dt><dd><?=money($detail['allowances'])?></dd></div>
        </dl></section>
        <section class="payslip-panel"><h3>Deductions</h3><dl>
            <div><dt>Total deductions</dt><dd><?=money($detail['deductions'])?></dd></div>
        </dl></section>
    </div>
    <div class="payslip-net"><div><span>Net salary</span><p>Earnings after deductions</p></div><strong><?=money($detail['net_salary'])?></strong></div>
    <div class="payslip-actions"><a class="btn btn-secondary" href="payslips.php">Back to salary records</a><button type="button" class="btn btn-primary" onclick="window.print()">Print / Save as PDF</button></div>
</section>
<?php endif; ?>
<section class="card" id="salaryOverview"><h2 class="card-title">My Salary Records</h2><div class="table-container"><table class="table"><thead><tr><th>Month</th><th>Basic</th><th>Allowances</th><th>Deductions</th><th>Net Salary</th><th>Status</th><th>Action</th></tr></thead><tbody>
<?php foreach ($rows as $s): ?><tr><td><?=h($s['month'])?></td><td><?=money($s['basic'])?></td><td><?=money($s['allowances'])?></td><td><?=money($s['deductions'])?></td><td><?=money($s['net_salary'])?></td><td><?=h($s['payment_status'])?></td><td><a class="btn btn-primary" href="?view=<?=(int)$s['id']?>">View Payslip</a></td></tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="7">No salary records available.</td></tr><?php endif; ?>
</tbody></table></div></section>
<?php page_end(); ?>
