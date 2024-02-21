<p>Todos los derechos reservados <?php echo date('Y'); ?> &#169;</p>

</body>

<?php if (DataAccessManager::get("persona")->isDeveloper(DataAccessManager::get("session")->getCurrentUser())): ?>
<script>
// Check if scenarios exist and prepare scenario buttons
if (scenarios && scenarios.length > 0) {
	insertStickyFooterWithId('scenarioButtons');
	prepareScenarioButtons(scenarios, 'scenarioButtons');
  
}
</script>
<?php endif; ?>


</html>
</html>
