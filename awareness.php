<?php
require_once 'config.php';
$page_title = 'Why e-waste matters';
$active = 'awareness.php';
include 'includes/header.php';
?>

<section class="hero" style="padding-block:3rem">
  <div class="wrap" style="max-width:70ch">
    <p class="eyebrow">Learn</p>
    <h1>Why e-waste can't go in the regular bin</h1>
    <p class="lead">
      Electronics are the fastest growing waste stream in the world. Most of it is
      still burned or buried, and almost all of it could be recovered instead.
    </p>
  </div>
</section>

<section>
  <div class="wrap grid grid-3">
    <article class="card">
      <div class="card-icon" aria-hidden="true">&#9760;</div>
      <h2 style="font-size:1.2rem">What's inside</h2>
      <p class="muted">Lead in solder, mercury in backlights, cadmium in older batteries,
        and brominated flame retardants in plastic casings.</p>
    </article>
    <article class="card">
      <div class="card-icon" aria-hidden="true">&#128167;</div>
      <h2 style="font-size:1.2rem">Where it ends up</h2>
      <p class="muted">In a landfill those metals leach into groundwater. Burned in the open
        to strip copper, they become fumes that informal workers breathe in daily.</p>
    </article>
    <article class="card">
      <div class="card-icon" aria-hidden="true">&#9851;</div>
      <h2 style="font-size:1.2rem">What's recoverable</h2>
      <p class="muted">Gold, silver, copper, palladium, aluminium and rare earth magnets —
        a tonne of circuit boards carries far more gold than a tonne of ore.</p>
    </article>
  </div>
</section>

<section style="background:var(--mint)">
  <div class="wrap" style="max-width:75ch">
    <h2>Before you hand over a device</h2>
    <ol>
      <li><strong>Back up anything you still want.</strong> Photos, documents, authenticator apps.</li>
      <li><strong>Sign out of accounts.</strong> Remove the Google or Apple account so the device isn't activation-locked.</li>
      <li><strong>Factory reset, then encrypt if you can.</strong> A reset on an encrypted phone makes old data unreadable.</li>
      <li><strong>Remove SIM and memory cards.</strong> These are easy to forget.</li>
      <li><strong>Tape battery terminals.</strong> Loose lithium cells can short and catch fire in transit.</li>
      <li><strong>Don't dismantle CRT monitors or CFL tubes yourself.</strong> They hold phosphor dust and mercury.</li>
    </ol>
  </div>
</section>

<section>
  <div class="wrap grid grid-2">
    <div>
      <h2>The rules in India</h2>
      <p>
        The E-Waste (Management) Rules make producers responsible for taking back what
        they sell, and require e-waste to reach authorised dismantlers and recyclers.
        In practice, most households never see that channel — which is the gap a service
        like this one tries to close.
      </p>
      <p class="muted">
        This is a student project, so treat the guidance here as a starting point rather
        than legal advice; always check the current rules on the CPCB website.
      </p>
    </div>
    <div class="panel">
      <h2 style="font-size:1.3rem">Quick myth check</h2>
      <p><strong>"A dead battery is harmless."</strong><br>
        <span class="muted">It still holds cadmium or lithium and can ignite when crushed.</span></p>
      <p><strong>"Deleting files is enough."</strong><br>
        <span class="muted">Deleted files stay recoverable until the drive is overwritten or encrypted.</span></p>
      <p style="margin:0"><strong>"Nobody wants a 10-year-old laptop."</strong><br>
        <span class="muted">Its aluminium, copper and board metals are still worth recovering.</span></p>
    </div>
  </div>
</section>

<section class="center">
  <div class="wrap">
    <h2>Ready to clear that drawer?</h2>
    <p class="muted">Book in a minute, and you'll know exactly where everything went.</p>
    <p><a class="btn btn-solid" href="request.php">Request a pickup</a></p>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
