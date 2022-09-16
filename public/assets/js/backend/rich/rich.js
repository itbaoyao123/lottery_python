define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'rich/rich/index' + location.search,
                    add_url: 'rich/rich/add',
                    edit_url: '',
                    del_url: 'rich/rich/del',
                    multi_url: 'rich/rich/multi',
                    import_url: 'rich/rich/import',
                    table: 'rich',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                escape:false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'red_num', title: '号码'},
                        {field: 'prize_money', title: '中奖金额'},
                        {field: 'is_bet', title: '是否购买', formatter: function (value, row, index) {
                                switch(value){
                                    case 1:
                                        return "<font color='red'>已购买</font>";
                                    case 0:
                                        return '未购买';
                                }
                            }, searchList: {"1":"是","2":"否"}},
                        {field: 'prize_number', title: '开奖号码'},
                        {field: 'lottery_date', title: '开奖日期'},
                        {field: 'number_periods', title: '期数'},
                        {field: 'type', title: '类型', formatter: function (value, row, index) {
                                switch(value){
                                    case 1:
                                        return '双色球';
                                    case 2:
                                        return '体彩';
                                }
                            }, searchList: {"1":"双色球","2":"体彩"}},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'status', title: '状态', formatter: function (value, row, index) {
                                switch(value){
                                    case 0:
                                        return '隐藏';
                                    case 1:
                                        return '正常';
                                }
                            }, searchList: {"0":"隐藏","1":"正常"}},
                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate},
                        {field: 'Operate', title: __('Operate'), table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'addtabs',
                                    text: '下注',
                                    icon: 'fa fa-ticket',
                                    classname: 'btn btn-xs btn-success btn-ajax',
                                    url: 'rich/rich/change_status',
                                    refresh:true
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        },
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            // 启动和暂停按钮
            $(document).on("click", ".btn-mkBall", function () {
                //在table外不可以使用添加.btn-change的方法
                //只能自己调用Table.api.multi实现
                //如果操作全部则ids可以置为空
                var ids = Table.api.selectedids(table);
                Table.api.multi("changestatus", ids.join(","), table, this);
            });
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        mkBall: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
