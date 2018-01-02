<?php

namespace OpenXBL;

use XF\AddOn\AbstractSetup;

use XF\Db\Schema\Create;

use XF\AddOn\StepRunnerInstallTrait;

use XF\AddOn\StepRunnerUninstallTrait;

use XF\AddOn\StepRunnerUpgradeTrait;

class Setup extends AbstractSetup
{

	use StepRunnerInstallTrait;

	use StepRunnerUpgradeTrait;

	use StepRunnerUninstallTrait;

    public function installStep1()
    {

		$this->db()
			->query("INSERT INTO xf_connected_account_provider VALUES ('openxbl', 'OpenXBL:Provider\\\\OpenXBL', 80, '')");

		$this->db()->getSchemaManager()->createTable('xf_openxbl_games', function (Create $table)
		{
			$table->addColumn('title_id', 'varchar', 150)->primaryKey();
			$table->addColumn('title', 'text');
			$table->addColumn('image', 'text');
			$table->addColumn('gamerscore', 'int');
			$table->addColumn('achievements', 'int');
			;
		});

		$this->db()->getSchemaManager()->createTable('xf_openxbl_users_games', function (Create $table)
		{
			$table->addColumn('user_id', 'int');
			$table->addColumn('title_id', 'varchar', 225);
			$table->addColumn('gamerscore', 'int');
			$table->addColumn('achievements', 'int');
			$table->addColumn('progress', 'int');
			$table->addColumn('last_played', 'text');
			;
		});

		$this->db()->getSchemaManager()->createTable('xf_openxbl_dvr', function (Create $table)
		{
			$table->addColumn('media_id', 'varchar', 150)->primaryKey();
			$table->addColumn('user_id', 'int');
			$table->addColumn('type', 'text');
			$table->addColumn('title', 'text')->nullable();
			$table->addColumn('caption', 'text')->nullable();
			$table->addColumn('game', 'text');
			$table->addColumn('duration', 'int');
			$table->addColumn('date', 'timestamp');
		});

    }

	public function uninstall(array $stepParams = [])
	{

		$this->db()
			->query('DELETE FROM xf_connected_account_provider WHERE provider_id = "openxbl" LIMIT 1');

		$this->schemaManager()->dropTable('xf_openxbl_games');

		$this->schemaManager()->dropTable('xf_openxbl_users_games');

		$this->schemaManager()->dropTable('xf_openxbl_dvr');
	}

}