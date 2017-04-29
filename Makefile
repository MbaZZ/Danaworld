currDir=$(shell realpath ./)
main:dockerImage
	@echo "dirName $(currDir)"

dockerImage:
	@echo "Building Docker image"
	@sudo docker build -f "$(currDir)/Dockerfile" "$(currDir)" -t danapache_base
    
clean:
	@echo "Cleanning solution"
	@sudo docker rmi danapache_base 

