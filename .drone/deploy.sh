# deploy to demo
ssh -i ~/.ssh/id_rsa $DEPLOY_USER@aplazame.com "cd $MAGENTO_PATH;modgit update Magento_Aplazame"
