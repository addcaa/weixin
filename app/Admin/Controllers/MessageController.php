<?php

namespace App\Admin\Controllers;

use App\Model\Message\MessageModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class MessageController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MessageModel);

        $grid->m_id('M id');
        $grid->openid('Openid');
        $grid->m_name('用户名');
        $grid->m_sex('性别')->display(function($sex){
            if($sex==1){
                return "男";
            }else if($sex==2){
                return "女";
            }else{
                return "隐藏";
            }
        });
        $grid->m_headimg('头像')->display(function($img){
            return '<img src="'.$img.'">';
        });
        $grid->m_time('时间')->display(function($time){
            return date('Y-m-d H:i:s',$time);
        });
        $grid->m_text('文字');
        $grid->m_image('图片')->display(function($img){
            return '<img src="'.$img.'">';
        });
        $grid->m_voice('语言');
        $grid->m_video('视频');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(MessageModel::findOrFail($id));

        $show->m_id('M id');
        $show->openid('Openid');
        $show->m_name('M name');
        $show->m_sex('M sex');
        $show->m_headimg('M headimg');
        $show->m_time('M time');
        $show->m_text('M text');
        $show->m_image('M image');
        $show->m_voice('M voice');
        $show->m_video('M video');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MessageModel);

        $form->number('m_id', 'M id');
        $form->text('openid', 'Openid');
        $form->text('m_name', 'M name');
        $form->text('m_sex', 'M sex');
        $form->text('m_headimg', 'M headimg');
        $form->text('m_time', 'M time');
        $form->text('m_text', 'M text');
        $form->text('m_image', 'M image');
        $form->text('m_voice', 'M voice');
        $form->text('m_video', 'M video');

        return $form;
    }
}
