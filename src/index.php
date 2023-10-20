<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php include "./.scripts/imports.php"; ?>
</head>
<body id="body">

    <nav>
        <?php Navigation::construct_nav(__DIR__); ?>
    </nav>

    <aside>
        <h3>Directions</h3>
        <div>
            <?php Navigation::construct_drawer(__DIR__); ?>
        </div>
    </aside>

    <section>
        <header>
            <h1>Strategiespiel</h1>
        </header>

        <article>
            <p>
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Repellendus, expedita enim ad corporis impedit maxime ex in nostrum velit repudiandae totam excepturi quos accusamus voluptate, debitis nobis nam hic ipsum!
            </p>
        </article>
    </section>

</body>
</html>
