<?php
namespace content_cp\permissions;

use \lib\utility;
use \lib\debug;

class model extends \content_cp\home\model
{
	function get_delete()
	{
		$this->qryPermission('delete');
		// var_dump(3);exit();
		// $this->delete( $this->sql()->table('posts')->where('id', $this->childparam('delete')));
	}

	function delete_delete()
	{
		$this->qryPermission('delete');
	}

	function post_delete()
	{
		$this->qryPermission('delete');
	}

	function post_add()
	{
		$this->qryPermission('add');
	}

	function put_edit()
	{
		$this->qryPermission('edit');
		// var_dump(4);exit();
	}


	protected function qryCreator($_type = null, $_value = null)
	{
		$qry = $this->sql()->table('options')
			->where('option_cat',  'permissions')
			// ->and('option_key',    'permissions')
			// ->and('option_status', 'enable')
			->and('post_id',       '#NULL')
			->and('user_id',       '#NULL');

		switch ($_type)
		{
			case 'add':
			case 'edit':
				$qry = $qry->and('option_value', $_value);
				break;

			case 'delete':
				$qry = $qry->and('option_value', $_value);
				$qry = $qry->and('option_status', 'enable');
				break;

			default:
				break;
		}
		return $qry;
	}


	/**
	 * create a related query and run it
	 * @param  [type] $_type [description]
	 * @return [type]        [description]
	 */
	protected function qryPermission($_type)
	{
		$newPerm = utility::post('pName');
		switch ($_type)
		{
			case 'add':
				if(!$newPerm)
				{
					debug::warn(T_("First you must enter name of permission"));
					return;
				}
				// check permission exist or not
				$qryExist = $this->qryCreator($_type);
				$qryExist = $qryExist->select()->num();
				// if exist show related message
				if($qryExist)
				{
					debug::warn(T_("This permission name exist!"). " "
						. T_("You can edit this permission"));
					return;
				}

				// get last id in permissions
				$qryMaxID = $this->qryCreator();
				$qryMaxID = $qryMaxID->field('#max(option_key) as id')
					->select()->assoc('id');

				$qryMaxID += 1;

				$qryAdd = $this->qryCreator();
				$qryAdd = $qryAdd
					->set('option_cat',    'permissions')
					->set('option_key',    $qryMaxID)
					->set('option_value',  $newPerm)
					->set('option_status', 'enable')
					->insert();

				$qryAdd = $qryAdd;

				break;

			case 'delete':
				$delParam = $this->childparam('delete');
				// if user pass child param, get this param and update status of permission
				if($delParam)
				{
					$qryDel = $this->qryCreator($_type, $delParam);
					$qryDel = $qryDel->set('option_status', 'disable')->update();
				}
				break;


			case 'edit':
				$editParam = $this->childparam('edit');

				if($editParam)
				{
					$myMeta = $this->permContents(false);
					// $myMeta['cp']['modules'] = $this->permModules('cp');

					foreach ($myMeta as $key => $value)
					{
						// step1: get and fill content enable status
						$postValue = utility::post('content-'.$key);
						$myMeta[$key]['enable'] = false;
						if($postValue === 'on')
							$myMeta[$key]['enable'] = true;


						$myMeta[$key]['modules'] = $this->permModules($key);
						// foreach ($myMeta[$key]['modules'] as $key => $value)
						// {

						// }
						// var_dump($key);
						// var_dump($myMeta[$key]['modules']);
					}




					var_dump($myMeta);
return;


					$qryEdit = $this->qryCreator($_type, $editParam);
					// var_dump($qryEdit);

					$myMeta = array_filter($myMeta);
					// var_dump($myMeta);
					// $qryEdit = $qryEdit->set('option_meta', $myMeta)->update();
				}
				break;


			default:
				break;
		}


		$this->commit(function($_type, $_permName)
		{
			switch ($_type)
			{
				case 'add':
					debug::true(T_("Insert Successfully"));
					$this->redirector()->set_url('permissions/' . $_permName);
					break;

				case 'delete':
					debug::true(T_("Delete Successfully"));
					break;

				case 'update':
					debug::true(T_("Update Successfully"));
					break;

				default:
					break;
			}
		}, $_type, $newPerm);

		// if a query has error or any error occour in any part of codes, run roolback
		$this->rollback(function()
		{
			debug::title(T_("Transaction error").': ');
		} );
	}


	/**
	 * draw list of permissions
	 * @return [type] return array contain list of permission and detail of it
	 */
	public function draw_permissions()
	{
		$pType = utility::get('name');

		$qry_result  = [];
		$qry = $this->sql()->table('options')
					->where('user_id', 'IS', 'NULL')
					->and('post_id', 'IS', "NULL")
					->and('option_cat', 'permissions')
					->and('option_status',"enable")
					// ->and('option_key', 'permissions')

					// ->groupOpen('g_status')
					// ->and('option_status', '=', "'enable'")
					// ->or('option_status', 'IS', "NULL")
					// ->or('option_status', "")
					// ->groupClose('g_status')
					;



		$datatable = $qry->select()->allassoc();

		foreach ($datatable as $key => $row)
		{
			$myMeta  = $row['option_meta'];
			if(substr($myMeta, 0,1) == '{')
			{
				$myMeta = json_decode($myMeta, true);
			}
			$qry_result[$row['option_value']] = $myMeta;
		}

		// on first level return result
		if(!$pType)
		{
			return $qry_result;
		}
		else
		{
			// var_dump($qry_result[$pType]);
			return $qry_result[$pType];
		}
	}

	/**
	 * read permission data and fill in array
	 * @param  [type] $_list [description]
	 * @return [type]        [description]
	 */
	public function permModulesFill($_content, $_list)
	{
		$permResult = [];
		$myChild    = $this->child();
		if($myChild === 'edit')
			$myChild = $this->childparam('edit');

		switch ($_content)
		{
			case 'cp':
				$qry = $this->sql()->table('options')
						->where('user_id',    'IS', 'NULL')
						->and('post_id',      'IS', "NULL")
						->and('option_cat',   'permissions')
						->and('option_value',  $myChild)
						->and('option_status', "enable");

				$datarow = $qry->select()->assoc('option_meta');
				if(substr($datarow, 0,1) == '{')
				{
					$datarow = json_decode($datarow, true);
				}
				// var_dump($datarow);
				break;

			default:
				break;
		}

		// var_dump($_content);
		if(isset($datarow[$_content]) && is_array($datarow[$_content]))
		{

			$myModules  = $datarow[$_content];

			foreach ($_list as $loc)
			{
				if(isset($myModules[$loc]) && is_array($myModules[$loc]))
					$permResult[$loc] = $myModules[$loc];
				else
					$permResult[$loc] = null;
			}
		}

		return $permResult;
	}
}
?>