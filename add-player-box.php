<?php
/**
 * "Add Players" one at a time. Shared by page-new-adventure.php and
 * page-manage-players.php.
 *
 * Most people being added already have an account somewhere in the system, so
 * this leads with a search over every field you'd recognise somebody by - first
 * name, last name, nickname, email - and lets you add straight from the results.
 * Creating a brand-new account is the fallback, offered only once the search has
 * actually come up empty.
 *
 * Expects $adv_child_id and $current_player from the including page.
 */
?>
<div class="br-add-player" id="br-add-player">

	<input type="hidden" id="br-ap-nonce" value="<?= wp_create_nonce('br_add_player_nonce'); ?>">
	<input type="hidden" id="br-ap-lang" value="<?= esc_attr($current_player->player_lang ?? ''); ?>">

	<div class="br-form-group">
		<label class="br-form-label" for="username-search"><?= __("Find someone already registered","bluerabbit"); ?></label>
		<span class="br-form-hint"><?= __("Search by first name, last name, nickname or email. Results show who they are and whether they are already in this adventure, so you can add several people one after another without reloading.","bluerabbit"); ?></span>
		<div class="br-ap-search-wrap">
			<span class="icon icon-search br-ap-search-icon"></span>
			<!-- id kept as username-search: the new-adventure tutorial anchors on it -->
			<input class="br-input br-ap-search" type="text" id="username-search" autocomplete="off"
				maxlength="255" placeholder="<?= esc_attr__("Type at least 2 characters…","bluerabbit"); ?>">
			<button type="button" class="br-ap-clear br-initially-hidden" id="br-ap-clear"
				title="<?= esc_attr__("Clear","bluerabbit"); ?>"><span class="icon icon-cancel"></span></button>
		</div>
	</div>

	<div class="br-ap-status" id="br-ap-status"></div>

	<div class="br-ap-results" id="br-ap-results"></div>

	<!-- Create a new account. Revealed by the search when nothing matched, or on
	     demand from the toggle, so the default path stays "find the person". -->
	<div class="br-ap-create br-initially-hidden" id="br-ap-create">
		<h4 class="br-ap-create-title"><span class="icon icon-add"></span> <?= __("Create a new account","bluerabbit"); ?></h4>
		<span class="br-form-hint"><?= __("Only the email is required. A free nickname is built from the email and a password is generated if you leave them blank — both are shown to you after the account is created.","bluerabbit"); ?></span>
		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Email","bluerabbit"); ?> <span class="br-ap-req">*</span></label>
				<input class="br-input" type="email" id="br-ap-new-email" maxlength="255" placeholder="<?= esc_attr__("name@company.com","bluerabbit"); ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Nickname","bluerabbit"); ?></label>
				<input class="br-input" type="text" id="br-ap-new-nickname" maxlength="50" placeholder="<?= esc_attr__("Optional","bluerabbit"); ?>">
			</div>
		</div>
		<div class="br-form-grid">
			<div class="br-form-group">
				<label class="br-form-label"><?= __("First Name","bluerabbit"); ?></label>
				<input class="br-input" type="text" id="br-ap-new-first" maxlength="100" placeholder="<?= esc_attr__("Optional","bluerabbit"); ?>">
			</div>
			<div class="br-form-group">
				<label class="br-form-label"><?= __("Last Name","bluerabbit"); ?></label>
				<input class="br-input" type="text" id="br-ap-new-last" maxlength="100" placeholder="<?= esc_attr__("Optional","bluerabbit"); ?>">
			</div>
		</div>
		<div class="br-form-group">
			<label class="br-form-label"><?= __("Password","bluerabbit"); ?></label>
			<input class="br-input" type="text" id="br-ap-new-password" maxlength="50" placeholder="<?= esc_attr__("Optional — generated if blank","bluerabbit"); ?>">
		</div>
		<div class="br-ap-create-error br-initially-hidden" id="br-ap-create-error"></div>
		<div class="br-actions">
			<button type="button" class="br-btn cyan" id="br-ap-create-btn" onClick="brApCreate();">
				<span class="icon icon-add"></span> <?= __("Create and add","bluerabbit"); ?>
			</button>
			<button type="button" class="br-btn ghost" onClick="brApToggleCreate(false);">
				<?= __("Cancel","bluerabbit"); ?>
			</button>
		</div>
	</div>

	<div class="br-actions br-ap-create-toggle" id="br-ap-create-toggle">
		<button type="button" class="br-btn ghost" onClick="brApToggleCreate(true);">
			<span class="icon icon-add"></span> <?= __("Can't find them? Create a new account","bluerabbit"); ?>
		</button>
	</div>

	<!-- Running record of this session's additions, so it is obvious what has
	     already been done and nothing needs to be re-checked by memory. -->
	<div class="br-ap-log br-initially-hidden" id="br-ap-log">
		<div class="br-ap-log-head">
			<span class="icon icon-check"></span>
			<span><?= __("Added in this session","bluerabbit"); ?></span>
			<span class="br-badge br-badge-green" id="br-ap-log-count">0</span>
			<button type="button" class="br-btn ghost br-btn-sm br-ap-refresh br-initially-hidden" id="br-ap-refresh"
				onClick="window.location.reload();">
				<span class="icon icon-rotate"></span> <?= __("Refresh the player list","bluerabbit"); ?>
			</button>
		</div>
		<ul class="br-ap-log-list" id="br-ap-log-list"></ul>
	</div>
</div>
