<ul class="sidebar-menu" data-widget="tree">
  <li class="header">MAIN NAVIGATION</li>
  <li>
    <a href="<?php echo $this->Url->build('/'); ?>">
      <i class="fa fa-home"></i> <span>Home</span>
    </a>
  </li>
  <li class="treeview">
    <a href="#">
      <i class="fa fa-folder"></i> <span>Menu</span>
      <span class="pull-right-container">
        <i class="fa fa-angle-left pull-right"></i>
      </span>
    </a>
    <ul class="treeview-menu">
      <li><a href="#"><i class="fa fa-circle-o"></i> Page 1</a></li>
      <li><a href="#"><i class="fa fa-circle-o"></i> Page 2</a></li>
      <li><a href="#"><i class="fa fa-circle-o"></i> Page 3</a></li>
    </ul>
  </li>
  <li><a href="<?php echo $this->Url->build('/pages/debug'); ?>"><i class="fa fa-bug"></i> <span>Debug</span></a></li>
</ul>
