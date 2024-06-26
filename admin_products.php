<?php

include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
   header('location:login.php');
};

if (isset($_POST['add_product'])) {

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $price = $_POST['price'];
   $quantity = $_POST['quantity'];
   $image = $_FILES['image']['name'];
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/' . $image;

   $select_product_name = mysqli_query($conn, "SELECT name FROM `products` WHERE name = '$name'") or die('query failed');

   if (mysqli_num_rows($select_product_name) > 0) {
      $message[] = 'product name already added';
   } else {
      $add_product_query = mysqli_query($conn, "INSERT INTO `products`(name, price, image, quantity) VALUES('$name', '$price', '$image','$quantity')") or die('query failed');

      if ($add_product_query) {
         if ($image_size > 2000000) {
            $message[] = 'image size is too large';
         } else {
            if (move_uploaded_file($image_tmp_name, $image_folder)) {
                $message[] = 'product added successfully!';
            } else {
                $message[] = 'Failed to upload image!';
            }
         }
      } else {
         $message[] = 'product could not be added!';
      }
   }
}

if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $delete_image_query = mysqli_query($conn, "SELECT image FROM `products` WHERE pid = '$delete_id'") or die('query failed');
   $fetch_delete_image = mysqli_fetch_assoc($delete_image_query);
   unlink('uploaded_img/' . $fetch_delete_image['image']);
   mysqli_query($conn, "DELETE FROM `products` WHERE pid = '$delete_id'") or die('query failed');
   header('location:admin_products.php');
}
if (isset($_POST['update_product'])) {

   $update_p_id = $_POST['update_p_id'];
   $update_name = mysqli_real_escape_string($conn, $_POST['update_name']);
   $update_price = mysqli_real_escape_string($conn, $_POST['update_price']);
   $update_quantity = mysqli_real_escape_string($conn, $_POST['update_quantity']);

   $update_query = "UPDATE `products` SET name = '$update_name', price = '$update_price', quantity = '$update_quantity' WHERE pid = '$update_p_id'";
   mysqli_query($conn, $update_query) or die(mysqli_error($conn));

   $update_image = $_FILES['update_image']['name'];
   $update_image_tmp_name = $_FILES['update_image']['tmp_name'];
   $update_image_size = $_FILES['update_image']['size'];
   $update_folder = 'uploaded_img/' . $update_image;
   $update_old_image = $_POST['update_old_image'];

   if (!empty($update_image)) {
      if ($update_image_size > 2000000) {
         $message[] = 'image file size is too large';
      } else {
         $update_image_query = "UPDATE `products` SET image = '$update_image' WHERE pid = '$update_p_id'";
         mysqli_query($conn, $update_image_query) or die(mysqli_error($conn));
         if (move_uploaded_file($update_image_tmp_name, $update_folder)) {
            unlink('uploaded_img/' . $update_old_image);
         } else {
            $message[] = 'Failed to upload updated image!';
         }
      }
   }

   header('location:admin_products.php');

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>products</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>

<body>

   <div class="flex-admin">
      <?php include 'admin_header.php'; ?>

      <!-- product CRUD section starts  -->

      <div class="product">
      <section class="add-products">

<h1 class="title">Shop products</h1>

<form action="" method="post" enctype="multipart/form-data">
   <h3>Add Product</h3>
   <input type="text" name="name" class="box" placeholder="Enter product name" required>
   <input type="number" min="0" name="price" class="box" placeholder="Enter product price" required>
   <input type="number" min="0" name="quantity" class="box" placeholder="Enter product quantity" required>
   <input type="text" name="company_name" class="box" placeholder="Company name" required>
   <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box" required>
   <input type="submit" value="add product" name="add_product" class="btn">
</form>

</section>

<!-- product CRUD section ends -->

<!-- show products  -->

<section class="show-products">

<div class="box-container">

   <?php
   $select_products = mysqli_query($conn, "SELECT * FROM `products`") or die('query failed');
   if (mysqli_num_rows($select_products) > 0) {
      while ($fetch_products = mysqli_fetch_assoc($select_products)) {
   ?>
         <div class="box">
            <img src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="">
            <div class="name"><?php echo $fetch_products['name']; ?></div>
            <div class="price">Rs <?php echo $fetch_products['price']; ?></div>
            <div class="quantity">
               <p class="qty-stock">Left in Stock</p>
               <span class="qty-num">
                  <?php echo $fetch_products['quantity']; ?>
               </span>
            </div>
            <div class="update-delete">
            <a href="admin_products.php?update=<?php echo $fetch_products['pid']; ?>" class="option-btn">update</a>
            <a href="admin_products.php?delete=<?php echo $fetch_products['pid']; ?>" class="delete-btn" onclick="return confirm('delete this product?');">delete</a>
            </div>
         </div>
   <?php
      }
   } else {
      echo '<p class="empty">no products added yet!</p>';
   }
   ?>
</div>

</section>

<section class="edit-product-form">

<?php
if (isset($_GET['update'])) {
   $update_id = $_GET['update'];
   $update_query = mysqli_query($conn, "SELECT * FROM `products` WHERE pid = '$update_id'") or die('query failed');
   if (mysqli_num_rows($update_query) > 0) {
      while ($fetch_update = mysqli_fetch_assoc($update_query)) {
?>
         <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="update_p_id" value="<?php echo $fetch_update['pid']; ?>">
            <input type="hidden" name="update_old_image" value="<?php echo $fetch_update['image']; ?>">
            <img src="uploaded_img/<?php echo $fetch_update['image']; ?>" alt="">
            <input type="text" name="update_name" value="<?php echo $fetch_update['name']; ?>" class="box" required placeholder="Enter product name">
            <input type="number" name="update_price" value="<?php echo $fetch_update['price']; ?>" min="0" class="box" required placeholder="Enter product price">
            <input type="number" name="update_quantity" value="<?php echo $fetch_update['quantity']; ?>" min="0" class="box" required placeholder="Enter product quantity">
            <input type="file" class="box" name="update_image" accept="image/jpg, image/jpeg, image/png">
            <input type="submit" value="update" name="update_product" class="btn">
            <input type="reset" value="canceled" id="close-update" class="option-btn">
         </form>
<?php
      }
   }
} else {
   echo '<script>document.querySelector(".edit-product-form").style.display = "none";</script>';
}
?>

</section>
      </div>

   </div>

   <!-- custom admin js file link  -->
   <script src="js/admin_script.js"></script>

</body>

</html>
