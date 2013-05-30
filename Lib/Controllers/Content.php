<?php namespace Think\Controllers;

use Think\Url;
use Think\Request;
use Think\Response;
use Think\Redirect;
use Think\Import;
use Think\Controller;
use Think\Debug;
use Think\Exception;
use Think\Library\Category;
use Think\Library\Upload\Upload;

/**
 * ContentExtendAction
 * 后台开发CURD及页面控制器
 */
class Content extends Controller {

	/**
	 * 主模型
	 * 默认由构造函数通过D或M方法声明
	 *
	 * @var object
	 */
	protected $model;

	/**
	 * 主模型名称
	 * 通过D()或M()方法声明
	 * 在构造函数内使用，require
	 *
	 * @var string
	 */
	protected $model_name;

	/**
	 * 上传图片匹配的尺寸名称
	 *
	 * @var string
	 */
	protected $image_thumb_name;

	/**
	 * 缩略图匹配的尺寸名称
	 *
	 * @var string
	 */
	protected $cover_thumb_name;

	// Category Config
	/**
	 * 分类Model的名称
	 * 默认为'category'表
	 * 可以为category表，也可以为内容表的子表
	 *
	 * @var string
	 */
	protected $category_model = 'Category';

	/**
	 * 自动定义category_id
	 * 在$this->set_category_id方法中被定义
	 * 当url中存在$this->category_query_name的get值，则自动获取定义category_id
	 *
	 * @var bealoon
	 */
	protected $category_id_auto_set = false;

	/**
	 * 是否必须提供$category_id
	 * 如果是，则必须在url中添加，否则返回404
	 *
	 * @var bealoon
	 */
	protected $category_id_require = false;

	/**
	 * 在category中的分类ID
	 * 也可以为内容子表的ID
	 *
	 * @var int
	 */
	protected $category_id;

	/**
	 * 分类表的ID在当前内容表中的外键名称
	 * 也可以代表内容父表在当前子表内的外键名称
	 *
	 * @var string
	 */
	protected $category_fk_name = 'cid';

	/**
	 * URL请求部分中query的名称
	 *
	 * @var string
	 */
	protected $category_query_name = 'cid';

	/**
	 * 每页显示多少行
	 *
	 * @var int
	 */
	protected $list_rows = 20;

	/**
	 * 主键名称
	 *
	 * @var string
	 */
	protected $pk_name = 'id';

	/**
	 * 获得的主键的值
	 *
	 * var int
	 */
	protected $pk_id;

	/**
	 * 查询条件
	 *
	 * @var array
	 */
	protected $condition = array();

	/**
	 * url中带有的query内容，由GET获得
	 *
	 * @var array
	 */
	protected $query = array();

	/**
	 * 列表内容的排序条件
	 *
	 * @var string
	 */
	protected $list_order = '';

	/**
	 * 构造函数
	 * 构建常用参数内容
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		// 创建主模型
		$this->model = D($this->model_name);

		// 保存id
		$this->pk_id = $_GET[$this->pk_name];

		// 输出缩略图名称
		if($this->cover_thumb_name)
		{
			$thumb = explode(',', $this->cover_thumb_name);
			$this->assign('coverThumbSize', $thumb[0]);
		}

		// 如果category_id没有被定义，则自动获取
		$this->set_category_id();

		// 保存所有query的信息，除$this->pk_name外
		$this->save_query();
	}

	/**
	 * 绑定查询钩子
	 *
	 * @param string $method
	 */
	public function on($method)
	{
		if(method_exists($this, $method))
		{
			$this->$method();
		}
	}

	/**
	 * Read List Action
	 * 用于显示主模型列表
	 * 具有页码筛选功能
	 *
	 * @return void
	 */
	public function index()
	{
		// 获得页码
		$page = $_GET['page'] ? $_GET['page'] : 1;

		// $this->category_fk_name 指向分类的外键
		if($_GET[$this->category_query_name])
		{
			$this->condition[$this->category_fk_name] = $_GET[$this->category_query_name];
		}

		// query查询前，通常用于设置查询条件
		$this->on('index_query_before');

		// 获得内容并输出页码数组
		$datas = $this->model->where($this->condition)->order($this->list_order)->page($page, $this->list_rows)->select();
		$this->assign('datas', $datas);

		$pager = $this->model->where($this->condition)->pager($page, $this->list_rows);
		$this->assign('pager', $pager);

		// query查询，通常用于添加补充设置
		$this->on('index_query_after');

		// 输出
		$this->display();
	}

	/**
	 * Create Action
	 * 用于创建新的数据行
	 *
	 * post请求下执行创建工作
	 * get请求下执行创建页面
	 *
	 * @return void
	 */
	public function get_create()
	{
		$this->assign('category', $this->category());
		$this->display('edit');
	}

	public function post_create()
	{
		$data = $this->model->create();
		$this->model->add($data);
		Redirect::success('创建成功', Url::make('index', $this->query));
	}

	/**
	 * Create Action by default data
	 * 通过默认方式创建数据行
	 * 创建完成后重定向至编辑操作
	 *
	 * @return void
	 */
	public function add()
	{
		$id = $this->model->createDefault(array($this->category_fk_name => $this->category_id));

		if($id)
		{
			$query = array_merge($this->query, array('id' => $id));

			Redirect::success('创建成功，请编辑', Url::make('edit', $query));
		}
		else {
			Redirect::error('创建失败，请重试');
		}
	}

	/**
	 * Update Action
	 * 编辑
	 *
	 * @return void
	 */
	public function get_edit()
	{
		if($this->pk_id)
		{
			$data = $this->model->find($this->pk_id);

			$this->on('get_edit_query_before');

			$this->assign('data', $data);
			$this->assign('category', $this->category());

			$this->on('get_edit_query_after');

			$this->display();
		}
		else {
			Response::_404('不存在id值');
		}
	}

	public function post_edit()
	{
		$data = $this->model->create();
		$this->model->save($data);
		Redirect::success('编辑成功', Url::make('index', $this->query));
	}

	/**
	 * Read Action
	 * 读取主模型数据
	 *
	 * @return void
	 */
	public function detail()
	{
		if($this->pk_id) {

			$data = $this->model->find($this->pk_id);
			$this->assign('data', $data);

			$this->on('detail_query_after');

			$this->display();
		}
	}

	/**
	 * Update model hidden
	 * 编辑model的hidden字段
	 *
	 * @return void
	 */
	public function enable()
	{
		if($this->pk_id) {

			$this->model->find($this->pk_id);
			$this->model->hidden = !$this->model->hidden;
			$this->model->save();

			Redirect::success('状态发布成功');
		}
	}

	/**
	 * Delete
	 * 删除
	 *
	 * @return void
	 */
	public function delete()
	{
		if($this->pk_id) {

			$this->model->delete($this->pk_id);
			Redirect::success('删除成功');
		}
	}

	/**
	 * Read category for sidebar widget
	 * 侧边栏调用小组件
	 *
	 * @return void
	 */
	public function sidebar()
	{
		// 侧边分栏
		$this->assign('category', $this->category());
		return $this->fetch('sidebar');
	}

	/**
	 * 文章内图片上传接口
	 *
	 * @return void
	 */
	public function image()
	{
		$info = Upload::image($_FILES['uploadify_file'], $image_thumb_name);
		Response::json($info);
	}

	/**
	 * Upload cover image and update 'coverpath'
	 * 封面上传并写入主模型
	 *
	 * @return void
	 */
	public function cover()
	{
		// 上传图片
		$info = Upload::image($_FILES['uploadify_file'], $this->cover_thumb_name);

		// 建立数据表
		$this->model->where(array('id' => $_POST['id']))->save(array('coverpath' => $info['name']));

		// 输出JSON
		Response::json($info);
	}

	/**
	 * Read category
	 *
	 * @return void
	 */
	protected function category()
	{
		$Category = new Category($this->category_model);
		return $Category->getList('', $this->category_id, 'priority ASC');
	}

	/**
	 * set_category_id获得方式
	 */
	protected function set_category_id()
	{
		if($this->category_id_require && !isset($_GET[$this->category_query_name]))
		{
			Debug::output(new Exception('没有提供:' . $this->category_query_name));
		}

		if($this->category_id_auto_set && isset($_GET[$this->category_query_name]))
		{
			$this->category_id = $_GET[$this->category_query_name];
		}
	}

	protected function save_query()
	{
		$this->query = array_merge($_GET, $this->query);
		if(isset($this->query[$this->pk_name]))
		{
			unset($this->query[$this->pk_name]);
		}
	}
}